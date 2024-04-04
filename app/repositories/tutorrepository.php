<?php

namespace Repositories;

use PDO;
use PDOException;
use Repositories\Repository;

class TutorRepository extends Repository
{
    function getAll($offset = NULL, $limit = NULL)
    {
        try {
            $query = "SELECT * FROM Tutors";
            if (isset($limit) && isset($offset)) {
                $query .= " LIMIT :limit OFFSET :offset ";
            }
            $stmt = $this->connection->prepare($query);
            if (isset($limit) && isset($offset)) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\Tutor');
            $tutors = $stmt->fetchAll();

            return $tutors;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getOne($id)
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM Tutors WHERE tutorId = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\Tutor');
            $tutor = $stmt->fetch();

            return $tutor;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function insert($tutor)
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO Tutors (userId, specialization, hourlyRate) VALUES (:userId, :specialization, :hourlyRate)");
            $stmt->bindParam(':userId', $tutor->userId);
            $stmt->bindParam(':specialization', $tutor->specialization);
            $stmt->bindParam(':hourlyRate', $tutor->hourlyRate);

            $stmt->execute();

            $tutor->tutorId = $this->connection->lastInsertId();

            return $tutor;
        } catch (PDOException $e) {
            echo $e;
        }
    }


    function update($tutor)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE Tutors SET userId = :userId, specialization = :specializations, hourlyRate = :hourlyRate WHERE tutorId = :tutorId");
            $stmt->bindParam(':tutorId', $tutor->tutorId);
            $stmt->bindParam(':userId', $tutor->userId);
            $stmt->bindParam(':specialization', $tutor->specialization);
            $stmt->bindParam(':hourlyRate', $tutor->hourlyRate);
            $stmt->execute();

            return $tutor;
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function delete($tutorId)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM Tutors WHERE tutorId = :tutorId");
            $stmt->bindParam(':tutorId', $tutorId);
            $stmt->execute();
            return;
        } catch (PDOException $e) {
            echo $e;
        }
        return true;
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
                a.appointmentTime BETWEEN :startTime AND :endTime
            ");

            $stmt->bindParam(':tutorId', $tutorId);
            $stmt->bindParam(':appointmentDate', $appointmentDate);
            $stmt->bindParam(':startTime', $startTime);
            $stmt->bindParam(':endTime', $endTime);
            $stmt->execute();

            $existingAppointments = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
            return $e;
        }
    }

    public function getTutorIdByUserId($userId) {
        try{
            $stmt = $this->connection->prepare("SELECT tutorId FROM Tutors WHERE userId = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e){
            return $e;
        }
    }
}
