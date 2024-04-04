<?php
namespace Services;

use Repositories\TutorRepository;

class TutorService {

    private $repository;

    function __construct()
    {
        $this->repository = new TutorRepository();
    }

    public function getAll($offset = NULL, $limit = NULL) {
        return $this->repository->getAll($offset, $limit);
    }

    public function getOne($tutorId) {
        return $this->repository->getOne($tutorId);
    }

    public function insert($tutor) {       
        return $this->repository->insert($item);        
    }

    public function update($tutor) {       
        return $this->repository->update($tutor);        
    }

    public function delete($tutorId) {       
        return $this->repository->delete($tutorId);        
    }

    public function getAvailableSlotsForTutor($tutorId, $appointmentDate) {
        return $this->repository->getAvailableSlotsForTutor($tutorId, $appointmentDate);
    }

    public function getTutorIdByUserId($userId) {
        return $this->repository->getTutorIdByUserId($userId);
    }
}

?>