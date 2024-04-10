<?php
namespace Models;

class Tutor extends User {
    public int $tutorId;
    public string $specialization;
    public string $firstName;
    public string $lastName;
    public float $hourlyRate;
    public string $profilePhoto;
}

?>