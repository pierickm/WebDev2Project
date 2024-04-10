<?php
namespace Services;

use Repositories\AppointmentRepository;

class AppointmentService {

    private $repository;

    function __construct()
    {
        $this->repository = new AppointmentRepository();
    }

    public function getAll($userId = null, $offset = 0, $limit = 10) {
        if ($userId !== null) {
            return $this->repository->getAll($userId, $offset, $limit);
        } else {
            return $this->repository->getAll($offset, $limit);
        }
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