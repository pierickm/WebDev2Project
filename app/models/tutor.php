<?php
namespace Models;

class Tutor extends User {
    public int $tutorId;
    public string $specializtion;
    public string $name;
    public double $hourlyRate;
}

?>