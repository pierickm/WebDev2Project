<?php

namespace Repositories;

use PDO;
use PDOException;
use Exception;
use Repositories\Repository;
use Models\User;

class UserRepository extends Repository
{
    function checkLogin($password, $emailAddress)
    {
        try {
            // retrieve the user with the given username
            $stmt = $this->connection->prepare("SELECT * FROM Users WHERE emailAddress = :emailAddress");
            $stmt->bindParam(':emailAddress', $emailAddress);
            $stmt->execute();

            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
            $user = $stmt->fetch();

            // verify if the password matches the hash in the database
            $result = $this->verifyPassword($password, $user->password);

            if (!$result)
                return false;

            // do not pass the password hash to the caller
            $user->password = "";
            return $user;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    // hash the password (currently uses bcrypt)
    function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // verify the password hash
    function verifyPassword($input, $hash)
    {
        return password_verify($input, $hash);
    }

    function delete($userId) {
        try{
            $stmt = $this->connection->prepare("DELETE FROM Users WHERE userId = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            return true;
        } catch( PDOException $e){
            throw new PDOException($e->getMessage());
        }
    }

    function getOne($userId) {
        try{
            $stmt = $this->connection->prepare("SELECT userId, emailAddress, firstName, lastName, userType, profilePhoto FROM Users WHERE userId = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    function getAll($limit, $offset) {
        try{
            $stmt = $this->connection->prepare("SELECT 
                Users.*, 
                Tutors.hourlyRate, 
                Tutors.specialization,
                Tutors.tutorId 
                 FROM Users 
                 LEFT JOIN Tutors ON Users.userId = Tutors.userId 
                LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function getTotalUsersCount() {
        try{
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Users");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    function checkExistingUser(User $user) {
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
    function register(User $user) {
        try {
            $emailInUse = $this->checkExistingUser($user);
            if(!$emailInUse){
                $stmt = $this->connection->prepare("INSERT INTO Users (emailAddress, firstName, lastName, password, userType) VALUES (:emailAddress, :firstName, :lastName, :password, :userType)");
                $stmt->bindParam(':emailAddress', $user->emailAddress);
                $stmt->bindParam(':firstName', $user->firstName);
                $stmt->bindParam(':lastName', $user->lastName);
                $stmt->bindParam(':password', $user->password);
                $stmt->bindParam(':userType', $user->userType);
                $stmt->execute();
        
                $insertedId = $this->connection->lastInsertId();
                $stmt = $this->connection->prepare("SELECT * FROM Users WHERE userId = :userId");
                $stmt->bindParam(':userId', $insertedId);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
                $insertedUser = $stmt->fetch();
                $insertedUser->password = "";
                return $insertedUser;
            } else{
                throw new Exception("This email is already used by another account.");
            }
           
        } catch (PDOException $e) {
            throw new PDOException("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Error: " . $e->getMessage());
        }
    }

    function update(User $user) {
        try{
            $stmt= $this->connection->prepare("UPDATE Users SET emailAddress = :emailAddress, firstName = :firstName, lastName = :lastName, userType = :userType, profilePhoto = :profilePhoto WHERE userId = :userId");
            $stmt->bindParam(':emailAddress', $user->emailAddress);
            $stmt->bindParam(':firstName', $user->firstName);
            $stmt->bindParam(':lastName', $user->lastName);
            $stmt->bindParam(':userType', $user->userType);
            $stmt->bindParam(':userId', $user->userId);
            $stmt->bindParam(':profilePhoto', $user->profilePhoto);

            $stmt->execute();
            return $this->getOne($user->userId);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function create(User $user) {
        try {
            $emailInUse = $this->checkExistingUser($user);
            if(!$emailInUse){
                $stmt = $this->connection->prepare("INSERT INTO Users (emailAddress, firstName, lastName, password, userType, profilePhoto) VALUES (:emailAddress, :firstName, :lastName, :password, :userType, :profilePhoto)");
                $stmt->bindParam(':emailAddress', $user->emailAddress);
                $stmt->bindParam(':firstName', $user->firstName);
                $stmt->bindParam(':lastName', $user->lastName);
                $stmt->bindParam(':password', $user->password);
                $stmt->bindParam(':userType', $user->userType);
                $stmt->bindParam(':profilePhoto', $user->profilePhoto);
                $stmt->execute();
        
                $insertedId = $this->connection->lastInsertId();
                
                $insertedUser = $this->getOne($insertedId);
                return $insertedUser;
            } else{
                throw new Exception("This email is already used by another account.");
            }
           
        } catch (PDOException $e) {
            throw new PDOException("Database error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Error: " . $e->getMessage());
        }
    }

    public function deleteTutorEntry($userId) {
        try {
            $stmt = $this->connection->prepare("DELETE FROM Tutors WHERE userId = :userId");
            $stmt->bindParam(":userId", $userId);
            $stmt->execute();

        } catch (PDOException $e) {
            throw new PDOException("Database error". $e->getMessage());
        }
    }
}