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

    public function getOne($userId) {
        return $this->repository->getOne($userId);
    }

    public function insert($tutor) {       
        return $this->repository->insert($tutor);        
    }

    public function update($tutor) {       
        return $this->repository->update($tutor);        
    }

    public function delete($userId) {       
        return $this->repository->delete($userId);        
    }

    public function getAvailableSlotsForTutor($tutorId, $appointmentDate) {
        return $this->repository->getAvailableSlotsForTutor($tutorId, $appointmentDate);
    }

    public function getTutorIdByUserId($userId) {
        return $this->repository->getTutorIdByUserId($userId);
    }

    public function hashPassword($password) {
        return $this->repository->hashPassword($password);
    }
}