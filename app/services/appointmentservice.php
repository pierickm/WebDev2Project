<?php
namespace Services;

use Repositories\AppointmentRepository;

class AppointmentService {

    private $repository;

    function __construct()
    {
        $this->repository = new AppointmentRepository();
    }

    public function getAll($offset = 0, $limit = 10, $userId = null) {
        if ($userId !== null) {
            return $this->repository->getAll($offset, $limit, $userId);
        } else {
            return $this->repository->getAll($offset, $limit);
        }
    }

    public function getAllForTutor($offset, $limit, $userId) {
        return $this->repository->getAllForTutor($offset, $limit, $userId);
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

    public function getTotalAppointmentsCount() {
        return $this->repository->getTotalAppointmentsCount();
    }
    
    public function getTotalAppointmentsCountForTutor($userId){
        return $this->repository->getTotalAppointmentsCountForTutor($userId);
    }

    public function getTotalAppointmentsCountForStudent($userId){
        return $this->repository->getTotalAppointmentsCountForStudent($userId);
    }
}

?>