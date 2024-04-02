<?php
namespace Models;

class User {
    enum UserType {
        case Student;
        case Tutor;
        case Administrator;
    }
    
    public int $userId;
    public string $firstName;
    public string $lastName;
    public string $password;
    public string $emailAddress;
    public UserType $userType;
}
?>