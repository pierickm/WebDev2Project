<?php

namespace Repositories;

use Models\Appointment;
use Models\Tutor;
use PDO;
use PDOException;
use Repositories\Repository;

class AppointmentRepository extends Repository
{
    function getAll($offset, $limit, $userId = null) {
        try {
            $query = "
                SELECT 
                    a.*,
                    student.firstName AS studentFirstName, 
                    student.lastName AS studentLastName, 
                    tutorUser.firstName AS tutorFirstName, 
                    tutorUser.lastName AS tutorLastName,
                    tutor.specialization AS topics
                FROM 
                    Appointments a
                LEFT JOIN 
                    Users student ON a.studentId = student.userId
                LEFT JOIN 
                    Tutors tutor ON a.tutorId = tutor.tutorId
                LEFT JOIN
                    Users tutorUser ON tutor.userId = tutorUser.userId
            ";
            
            // Add condition for user-specific appointments
            if ($userId !== null) {
                $query .= " WHERE a.studentId = :userId";
            }
            
            $query .= " LIMIT :limit OFFSET :offset";
            
            $stmt = $this->connection->prepare($query);
            
            if ($userId !== null) {
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
    
    public function getAllForTutor($offset, $limit, $userId) {
        try {
            $query = "
            SELECT 
                a.*,
                student.firstName AS studentFirstName, 
                student.lastName AS studentLastName, 
                tutorUser.firstName AS tutorFirstName, 
                tutorUser.lastName AS tutorLastName,
                tutor.specialization AS topics
            FROM 
                Appointments a
            LEFT JOIN 
                Users student ON a.studentId = student.userId
            LEFT JOIN 
                Tutors tutor ON a.tutorId = tutor.tutorId
            LEFT JOIN
                Users tutorUser ON tutor.userId = tutorUser.userId
            WHERE 
                tutor.userId = :userId
            LIMIT :limit OFFSET :offset";
        
            
            $stmt = $this->connection->prepare($query);
            
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
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

    public function getTotalAppointmentsCount() {
        try{
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Appointments");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function getTotalAppointmentsCountForTutor($userId) {
        try {
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) 
                FROM Appointments 
                INNER JOIN Tutors ON Appointments.tutorId = Tutors.tutorId
                WHERE Tutors.userId = :userId
            ");
            
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
    

    public function getTotalAppointmentsCountForStudent($userId) {
        try{
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Appointments WHERE studentId = :studentId");
            $stmt->bindParam(':studentId', $userId);
            $stmt->execute();
            return $stmt->fetchColumn();
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
