<?php
namespace Models;

class Appointment
{

    const STATUS_PENDING = 'Pending';
    const STATUS_CONFIRMED = 'Confirmed';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_CANCELLED = 'Cancelled';

    public int $appointmentId;
    public int $studentId;
    public int $tutorId;
    public string $appointmentDate;
    public string $appointmentTime;
    public string $comment;
    public string $status;

    public function __construct()
    {
        $this->appointmentId = 0;
        $this->status = self::STATUS_CONFIRMED;
    }
}
?>