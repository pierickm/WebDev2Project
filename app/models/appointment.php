<?php
namespace Models;

class Appointment {

    enum Status {
        case Pending;
        case Confirmed;
        case Completed;
        case Cancelled;
    }

    public int $appointmentId;
    public int $studentId;
    public int $tutorId;
    public date $appointmentDate;
    public time $appointmentTime;
    public string $comment;
    public Status $status;
 
}

?>