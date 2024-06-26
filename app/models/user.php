<?php
namespace Models;

class User
{
    const UserType_Student = 'Student';
    const UserType_Tutor = 'Tutor';
    const UserType_Administrator = 'Administrator';

    public ?int $userId = null;
    public string $firstName;
    public string $lastName;
    public string $password;
    public string $emailAddress;
    public string $userType;
    public ?string $profilePhoto;
    public $deleteTutorEntry = false;

}
?>