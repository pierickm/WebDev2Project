<?php

namespace Controllers;

use Exception;
use Services\TutorService;

class TutorController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        parent::__construct();
        $this->service = new TutorService();
    }

    public function getAll()
    {
        try{
            $decodedJwt = $this->verifyToken();
            if(!$decodedJwt){
                return;
            }

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] :20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] :0;

            $tutors = $this->service->getAll($limit, $offset);
            $this->respond($tutors);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getOne($tutorId)
    {
        try {
            $decodedJwt = $this->verifyToken();
        
            if(!$decodedJwt){
                return;
            }

            $tutor = $this->service->getOne($tutorId);
            if($tutor) {
                $this->respond($tutor);
            } else {
                $this->respndWithError(404, "Tutor not found");
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function create()
    {
        try{
            $decodedJwt = $this->verifyToken();
            if(!$decodedJwt){
                return;
            }
            
            if(!$decodedJwt->data->userType == "Administrator") {
                $this->respondWithError(403, "Unauthorized access. Only administrators have access to this.");
                return;
            }

            $tutor = $this->createObjectFromPostedJson("Models\\Tutor");
            $this->service->insert($tutor);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($tutor);
    }

    public function update($tutorId)
    {
        try {
            $decodedJwt = $this->verifyToken();
            if(!$decodedJwt){
                return;
            }
            $tutor = $this->createObjectFromPostedJson("Models\\Tutor");
            $tutor->userId = $decodedJwt->data->userId;

            $usersTutorId = $this->service->getTutorIdByUserId($tutor->userId);
            
            if (!($decodedJwt->data->userType == "Administrator" || $tutorId == $usersTutorId)) {
                $this->respondWithError(403, "Forbidden - You are not authorized to update this account.");
                return;
            }

            $updatedTutor = $this->service->update($tutor);
            $this->respond($updatedTutor);
        } catch(Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function delete($tutorId)
    {
        try {
            $decodedJwt = $this->verifyToken();
            if(!$decodedJwt){
                return;
            }
            
            if(!$decodedJwt->data->userType == "Administrator") {
                $this->respondWithError(403, "Unauthorized access. Only administrators have access to this.");
                return;
            }
            $this->service->delete($tutorId);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(true);
    }

    public function getAvailableSlotsForTutor($tutorId, $appointmentDate)
    {
        try {
            $decodedHeader = $this->verifyToken();
            if(!$decodedHeader){
                return;
            }
            $availableSlots = $this->service->getAvailableSlotsForTutor($tutorId, $appointmentDate);

            if (!$availableSlots) {
                $this->respondWithError(404, "No available slots found");
                return;
            }
            return $this->respond($availableSlots);
        } catch (Exception $e) {
            return $this->respondWithError(500, $e->getMessage());
        }
    }
}
