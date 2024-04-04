<?php
namespace Services;

use Repositories\AppointmentRepository;

class AppointmentService {

    private $repository;

    function __construct()
    {
        $this->repository = new AppointmentRepository();
    }

    public function getAll($offset, $limit) {
        return $this->repository->getAll($offset, $limit);
    }

    public function getAll($userId, $offset, $limit) {
        return $this->repository->getAll($userId, $offset, $limit);
    }
    
    public function getOne($id) {
        return $this->repository->getOne($id);
    }

    public function create($appointment) {       
        return $this->repository->create($appointment);        
    }

    public function update($appointment) {       
        return $this->repository->update($appointment);        
    }

    public function delete($appointment) {       
        return $this->repository->delete($appointment);        
    }

    public function checkAppointmentAvailability($tutorId, $appointmentDate, $appointmentTime){
        return $this->repository->checkAppointmentAvailability($tutorId, $appointmentDate, $appointmentTime);
    }
}

?>