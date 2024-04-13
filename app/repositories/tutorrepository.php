<?php

namespace Repositories;

use PDO;
use PDOException;
use Repositories\Repository;
use Exception;

class TutorRepository extends Repository
{
    function getAll($offset = NULL, $limit = NULL)
    {
        try {
            $query = "SELECT T.*, U.firstName, U.lastName, U.profilePhoto FROM Tutors T 
                        INNER JOIN Users U ON T.userId = U.userId";

            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $tutors = $stmt->fetchAll(PDO::FETCH_CLASS, 'Models\Tutor');

            return $tutors;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    function getOne($id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT T.*, U.firstName, U.lastName, U.emailAddress, U.profilePhoto, U.userType FROM Tutors T 
                        INNER JOIN Users U ON T.userId = U.userId WHERE T.userId = :userId");
            $stmt->bindParam(':userId', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\Tutor');
            $tutor = $stmt->fetch();

            return $tutor;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function getTotalTutorsCount() {
        try{
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Tutors");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    function insert($tutor)
    {
        try {
            $this->connection->beginTransaction();
            if($tutor->userId == null) {
               $tutor->userId = $this->insertUserData($tutor);
            }

            $stmt = $this->connection->prepare("INSERT INTO Tutors (userId, specialization, hourlyRate) VALUES (:userId, :specialization, :hourlyRate)");
            $stmt->bindParam(':userId', $tutor->userId);
            $stmt->bindParam(':specialization', $tutor->specialization);
            $stmt->bindParam(':hourlyRate', $tutor->hourlyRate);

            $stmt->execute();
            

            $tutor->tutorId = $this->connection->lastInsertId();

            $this->connection->commit();

            return $this->getOne($tutor->userId);
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new PDOException($e->getMessage());
        }
    }

    private function insertUserData($user) {
        try {
            $emailInUse = $this->checkExistingUser($user);
            if(!$emailInUse){
                $hashedPassword = $this->hashPassword($user->password);
                $stmt = $this->connection->prepare("INSERT INTO Users (emailAddress, firstName, lastName, password, userType, profilePhoto) VALUES (:emailAddress, :firstName, :lastName, :password, :userType, :profilePhoto)");
                $stmt->bindParam(':emailAddress', $user->emailAddress);
                $stmt->bindParam(':firstName', $user->firstName);
                $stmt->bindParam(':lastName', $user->lastName);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':userType', $user->userType);
                $stmt->bindParam(':profilePhoto', $user->profilePhoto);
                $stmt->execute();
        
                return $this->connection->lastInsertId();
            } else{
                throw new Exception("This email is already used by another account.");
            }
           
        } catch (PDOException $e) {
            throw new PDOException("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Error: " . $e->getMessage());
        }
    }

    function update($tutor)
    {
        try {
            // Start a transaction
            $this->connection->beginTransaction();

            // Update the Tutors table
            $tutorStmt = $this->connection->prepare("UPDATE Tutors SET specialization = :specialization, hourlyRate = :hourlyRate WHERE tutorId = :tutorId");
            $tutorStmt->bindParam(':tutorId', $tutor->tutorId);
            $tutorStmt->bindParam(':specialization', $tutor->specialization);
            $tutorStmt->bindParam(':hourlyRate', $tutor->hourlyRate);
            $tutorStmt->execute();

            // Update the Users table
            $userStmt = $this->connection->prepare("UPDATE Users SET firstName = :firstName, lastName = :lastName, emailAddress = :emailAddress, profilePhoto = :profilePhoto WHERE userId = :userId");
            $userStmt->bindParam(':userId', $tutor->userId);
            $userStmt->bindParam(':firstName', $tutor->firstName);
            $userStmt->bindParam(':lastName', $tutor->lastName);
            $userStmt->bindParam(':emailAddress', $tutor->emailAddress);
            $userStmt->bindParam(':profilePhoto', $tutor->profilePhoto);
            $userStmt->execute();

            // Commit the transaction
            $this->connection->commit();

            return $this->getOne($tutor->userId);
        } catch (PDOException $e) {
            // Roll back the transaction on error
            $this->connection->rollBack();
            throw new PDOException($e->getMessage());
        }
    }


    function delete($userId) {
        try {
            // Begin a transaction
            $this->connection->beginTransaction();

            // First, delete from Tutors table if the user is a tutor
            $stmtTutors = $this->connection->prepare("DELETE FROM Tutors WHERE userId = :userId");
            $stmtTutors->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtTutors->execute();

            // Then, delete from Users table
            $stmtUsers = $this->connection->prepare("DELETE FROM Users WHERE userId = :userId");
            $stmtUsers->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtUsers->execute();

            // Commit the transaction
            $this->connection->commit();

            return true;
        } catch (PDOException $e) {
            // Roll back the transaction if something failed
            $this->connection->rollBack();
            throw new PDOException($e->getMessage());
        }
    }

    public function getAvailableSlotsForTutor($tutorId, $appointmentDate)
    {
        
        try {
            $fixedAppointmentLength = 45;
            $startTime = '09:00';
            $endTime = '17:00';

            $stmt = $this->connection->prepare("
            SELECT 
                a.appointmentTime
            FROM
                Appointments a
            WHERE
                a.tutorId = :tutorId AND 
                a.appointmentDate = :appointmentDate AND 
                a.appointmentTime BETWEEN :startTime AND :endTime AND
                a.status = :status
            ");

            $stmt->bindParam(':tutorId', $tutorId);
            $stmt->bindParam(':appointmentDate', $appointmentDate);
            $stmt->bindParam(':startTime', $startTime);
            $stmt->bindParam(':endTime', $endTime);
            $stmt->bindValue(':status', 'Confirmed');
            $stmt->execute();

            $existingAppointments = $stmt->fetchAll(PDO::FETCH_COLUMN);
            // Format existing appointments to "H:i"
            $existingAppointments = array_map(function($time) {
                return substr($time, 0, 5); // Converts "HH:MM:SS" to "HH:MM"
            }, $existingAppointments);

        
            $availableSlots = [];
            $currentTime = strtotime($startTime);
            while ($currentTime < strtotime($endTime)) {
                $appointmentTime = date('H:i', $currentTime);
                if (!in_array($appointmentTime, $existingAppointments)) {
                    $availableSlots[] = $appointmentTime;
                }
                $currentTime = strtotime("+$fixedAppointmentLength minutes", $currentTime);
            }

            return $availableSlots;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function getTutorIdByUserId($userId) {
        try{
            $stmt = $this->connection->prepare("SELECT tutorId FROM Tutors WHERE userId = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e){
            throw new PDOException($e->getMessage());
        }
    }

    function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    function checkExistingUser($user) {
        try{
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Users WHERE emailAddress = :emailAddress");
            $stmt->bindParam(":emailAddress", $user->emailAddress);
            $stmt->execute();

            if($stmt->fetchColumn() > 0){
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
}
