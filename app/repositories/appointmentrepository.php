<?php

namespace Repositories;

use Models\Appointment;
use Models\Tutor;
use PDO;
use PDOException;
use Repositories\Repository;

class AppointmentRepository extends Repository
{
    function getAll($offset, $limit, $userId = null)
    {
        try {
            if ($userId !== null) {
                $stmt = $this->connection->prepare("
                    SELECT 
                        a.*, 
                        u.*, 
                        t.* 
                    FROM 
                        Appointments a
                    LEFT JOIN 
                        Userss s ON a.studentId = u.userId
                    LEFT JOIN 
                        Tutors t ON a.tutorId = t.tutorId
                    WHERE a.studentId = :userId OR a.tutorId = :userId
                    LIMIT :limit OFFSET :offset
                ");
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            } else {
                $stmt = $this->connection->prepare("
                    SELECT 
                        a.*, 
                        u.*, 
                        t.* 
                    FROM 
                        Appointments a
                    LEFT JOIN 
                        Users s ON a.studentId = u.userId
                    LEFT JOIN 
                        Tutors t ON a.tutorId = t.tutorId
                    LIMIT :limit OFFSET :offset
                ");
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    function getOne($id)
    {
        try {
            $query = "SELECT 
            a.*, 
            u.*, 
            t.* 
            FROM 
                Appointments a
            LEFT JOIN 
                Users u ON a.studentId = u.userId
            LEFT JOIN 
                Tutors t ON a.tutorId = t.tutorId
            WHERE a.appointmentId = :appointmentId";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':appointmentId', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $appointment = $stmt->fetch();

            return $appointment;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }


    function create($appointment)
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO Appointments (studentId, tutorId, appointmentDate, appointmentTime, comment, status) VALUES (:studentId, :tutorId, :appointmentDate, :appointmentTime, :comment, :status)");
            $stmt->bindParam(':studentId', $appointment->studentId);
            $stmt->bindParam(':tutorId', $appointment->tutorId);
            $stmt->bindParam(':appointmentDate', $appointment->appointmentDate);
            $stmt->bindParam(':appointmentTime', $appointment->appointmentTime);
            $stmt->bindParam(':comment', $appointment->comment);
            $stmt->bindParam(':status', $appointment->status);
            
            $stmt->execute();

            $appointment->appointmentId = $this->connection->lastInsertId();

            return $appointment;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }


    function update($appointment)
    {
        try {
            $stmt = $this->connection->prepare("UPDATE Appointments SET studentId = :studentId, tutorId = :tutorId, appointmentDate = :appointmentDate, appointmentTime = :appointmentTime, comment = :comment, status = :status WHERE appointmentId = :appointmentId");
            $stmt->bindParam(':appointmentId', $appointment->appointmentId);
            $stmt->bindParam(':studentId', $appointment->studentId);
            $stmt->bindParam(':tutorId', $appointment->tutorId);
            $stmt->bindParam(':appointmentDate', $appointment->appointmentDate);
            $stmt->bindParam(':appointmentTime', $appointment->appointmentTime);
            $stmt->bindParam(':comment', $appointment->comment);
            $stmt->bindParam(':status', $appointment->status);
            $stmt->execute();

            return $this->getOne($appointment->appointmentId);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    function delete($id)
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM Appointments WHERE appointmentId = :appointmentId");
            $stmt->bindParam(':appointmentId', $id);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    function checkAppointmentAvailability($tutorId, $appointmentDate, $appointmentTime) {
        try {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) AS count
                FROM Appointments
                WHERE tutorId = :tutorId 
                AND appointmentDate = :appointmentDate 
                AND appointmentTime = :appointmentTime
            ");
    
            $formattedAppointmentDate = date('Y-m-d', strtotime($appointmentDate));
            $formattedAppointmentTime = date('H:i:s', strtotime($appointmentTime));
    
            $stmt->bindParam(':tutorId', $tutorId, PDO::PARAM_INT);
            $stmt->bindParam(':appointmentDate', $formattedAppointmentDate, PDO::PARAM_STR);
            $stmt->bindParam(':appointmentTime', $formattedAppointmentTime, PDO::PARAM_STR);
            $stmt->execute();
    
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $appointmentCount = $result['count'];
    
            return ($appointmentCount === 0);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
    
}
