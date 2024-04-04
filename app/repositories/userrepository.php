<?php

namespace Repositories;

use PDO;
use PDOException;
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
            return $e;
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
            return $e;
        }
    }

    function getOne($userId) {
        try{
            $stmt = $this->connection->prepare("SELECT userId, emailAddress, firstName, lastName, userType FROM Users WHERE userId = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $e;
        }
    }

    function getAll($limit, $offset) {
        try{
            $stmt = $this->connection->prepare("SELECT * FROM Users LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return $e;
        }
    }

    function register(User $user) {
        try {
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
            $insertedUser = $stmt->fetch(PDO::FETCH_ASSOC);
            $insertedUser->password = "";
        return $insertedUser;
        } catch (PDOException $e) {
            return $e;
        }
    }

    function update(User $user) {
        try{
            $stmt= $this->connection->prepare("UPDATE Users SET emailAddress = :emailAddress, firstName = :firstName, lastName = :lastName, userType = :userType WHERE userId = :userId");
            $stmt->bindParam(':emailAddress', $user->emailAddress);
            $stmt->bindParam(':firstName', $user->firstName);
            $stmt->bindParam(':lastName', $user->lastName);
            $stmt->bindParam(':userType', $user->userType);
            $stmt->bindParam(':userId', $user->userId);

            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return $e;
        }
    }
}
