<?php
namespace Models;

class User {
    const UserType_Student = 'Student';
    const UserType_Tutor = 'Tutor';
    const UserType_Administrator = 'Administrator';
    
    public int $userId;
    public string $firstName;
    public string $lastName;
    public string $password;
    public string $emailAddress;
    public string $userType;
}
?>