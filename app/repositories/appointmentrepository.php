<?php

namespace Repositories;

use Models\Appointment;
use Models\Tutor;
use PDO;
use PDOException;
use Repositories\Repository;

class AppointmentRepository extends Repository
{
    function getAll($offset, $limit)
    {
        try {
            $stmt = $this->connection->prepare("
            SELECT 
                a.*, 
                s.*, 
                t.* 
            FROM 
                Appointments a
            LEFT JOIN 
                Students s ON a.studentId = s.userId
            LEFT JOIN 
                Tutors t ON a.tutorId = t.userId
            LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getAll($userId, $offset, $limit)
    {
        try {
            $stmt = $this->connection->prepare("
            SELECT 
                a.*, 
                s.*, 
                t.* 
            FROM 
                Appointments a
            LEFT JOIN 
                Students s ON a.studentId = s.userId
            LEFT JOIN 
                Tutors t ON a.tutorId = t.userId
            WHERE a.studentId = :userId OR a.tutorId = :userId
            LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
        }
    }

    function getOne($id)
    {
        try {
            $query = "SELECT 
            a.*, 
            s.*, 
            t.* 
        FROM 
            Appointments a
        LEFT JOIN 
            Students s ON a.studentId = s.userId
        LEFT JOIN 
            Tutors t ON a.tutorId = t.userId";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $appointment = $stmt->fetch();

            return $appointment;
        } catch (PDOException $e) {
            echo $e;
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

            $appointment->id = $this->connection->lastInsertId();

            return $this->getOne($appointment->appointmentId);
        } catch (PDOException $e) {
            echo $e;
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
            echo $e;
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
            echo $e;
        }
    }

    function checkAppointmentAvailability($tutorId, $appointmentDate, $appointmentTime) {
        try{
            $stmt = $this->connection->prepare("
            SELECT COUNT(*) AS count
            FROM Appointments
            WHERE tutorId = :tutorId 
            AND appointmentDate = :appointmentDate 
            AND appointmentTime = :appointmentTime
            ");

            $stmt->bindParam(':tutorId', $tutorId);
            $stmt->bindParam(':appointmentDate', $appointmentDate);
            $stmt->bindParam(':appointmentTime', $appointmentTime);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $appointmentCount = $result['count'];

            return ($appointmentCount === '0');
        } catch (PDOException $e) {
            return $e;
        }
    }
}
