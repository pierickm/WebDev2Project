<?php

namespace Controllers;

require __DIR__ .'/../Validation.php';
use Exception;
use Services\TutorService;
use Validation;
use DateTime;

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
            $limit = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) : 20;
            $offset = isset($_GET['offset']) ? filter_var($_GET['offset'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]) : 0;

            $tutors = $this->service->getAll($offset, $limit);
            
            $total = $this->service->getTotalTutorsCount();

            $response = [
                'data' => $tutors,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ];
        
            $this->respond($response);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getOne($userId)
    {
        try {
            $decodedJwt = $this->verifyToken();
        
            if(!$decodedJwt){
                return;
            }
            $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);

            $tutor = $this->service->getOne($userId);
            if($tutor) {
                $this->respond($tutor);
            } else {
                $this->respondWithError(404, "Tutor not found");
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function create()
{
    try {
        $decodedJwt = $this->verifyToken();
        if (!$decodedJwt) {
            return;
        }
        
        if ($decodedJwt->data->userType !== "Administrator") {
            $this->respondWithError(403, "Unauthorized access. Only administrators have access to this.");
            return;
        }

        $tutor = $this->createObjectFromPostedJson("Models\\Tutor");
        $requiredFields = $tutor->userId ? ['hourlyRate', 'specialization'] : ['emailAddress', 'password', 'firstName', 'lastName', 'userType', 'hourlyRate', 'specialization'];
        $emailFields = $tutor->userId ? [] : ['emailAddress'];

        $validator = new Validation((array)$tutor, $requiredFields, $emailFields);
        $errors = $validator->validate();
        if (!$validator->isValid()) {
            $this->respondWithError(400, $errors);
            return;
        }

        $createdTutor = $this->service->insert($tutor);
        if ($createdTutor) {
            $this->respond($createdTutor);
        } else {
            $this->respondWithError(500, 'An error occurred while creating the tutor.');
        }
    } catch (Exception $e) {
        $this->respondWithError(500, $e->getMessage());
    }
}


    public function update($userId)
    {
        try {
            $decodedJwt = $this->verifyToken();
            if(!$decodedJwt){
                return;
            }
            $tutor = $this->createObjectFromPostedJson("Models\\Tutor");
            if($decodedJwt->data->userType == "Tutor"){
                $tutor->userId = $decodedJwt->data->userId;
            }

            $validator = new Validation((array)$tutor, ['emailAddress', 'firstName', 'lastName', 'userType', 'hourlyRate', 'specialization'], ['emailAddress']);
            $errors = $validator->validate();
            if (!$validator->isValid()) {
                $this->respondWithError(400, $errors);
                return;
            }

            $usersTutorId = $this->service->getTutorIdByUserId($tutor->userId);
            
            if (!$decodedJwt->data->userType == "Administrator" && !$tutor->tutorId == $usersTutorId) {
                $this->respondWithError(403, "Forbidden - You are not authorized to update this account.");
                return;
            }

            $updatedTutor = $this->service->update($tutor);
            $this->respond($updatedTutor);
        } catch(Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function delete($userId)
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

            $userId = filter_var($userId, FILTER_SANITIZE_STRING);

            $this->service->delete($userId);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(true);
    }

    public function getAvailableSlotsForTutor()
    {
        try {
            $decodedHeader = $this->verifyToken();
            if(!$decodedHeader){
                return;
            }
            
            $tutorId = isset($_GET['tutorId']) ? filter_var($_GET['tutorId'], FILTER_VALIDATE_INT) : null;
            $appointmentDate = isset($_GET['appointmentDate']) ? $_GET['appointmentDate'] : null;
            try {
                $date = new DateTime($appointmentDate);
                $appointmentDate = $date->format('Y-m-d');
            } catch (Exception $e) {
                $this->respondWithError(400, "Invalid date format.");
                return;
            }
    
            if (!$tutorId || !$appointmentDate) {
                $this->respondWithError(400, "Both tutorId and appointmentDate are required");
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
