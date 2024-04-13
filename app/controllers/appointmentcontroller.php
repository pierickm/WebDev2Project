<?php

namespace Controllers;

require __DIR__ .'/../Validation.php';

use Exception;
use Services\AppointmentService;
use Validation;
class AppointmentController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        parent::__construct();
        $this->service = new AppointmentService();
    }

    public function getAll()
{
    try {
        $decodedHeader = $this->verifyToken();
        if (!$decodedHeader) {
            return;
        }
        
        $userId = $decodedHeader->data->userId;
        $userType = $decodedHeader->data->userType;

        $limit = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) : 20;
        $offset = isset($_GET['offset']) ? filter_var($_GET['offset'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]) : 0;


        $appointments = [];
        $total = 0;

        if ($userType == 'Administrator') {
            $appointments = $this->service->getAll($offset, $limit);
            $total = $this->service->getTotalAppointmentsCount();
        } elseif ($userType == 'Tutor') {
            $appointments = $this->service->getAllForTutor($offset, $limit, $userId);
            $total = $this->service->getTotalAppointmentsCountForTutor($userId);
        } else {
            $appointments = $this->service->getAll($offset, $limit, $userId);
            $total = $this->service->getTotalAppointmentsCountForStudent($userId);
        }

        $response = [
            'data' => $appointments,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];

        $this->respond($response);
    } catch (Exception $e) {
        $this->respondWithError(500, $e->getMessage());
    }
}


    public function getOne($appointmentId)
    {
        try{
            $decodedHeader = $this->verifyToken();
            if(!$decodedHeader){
                return;
            }

            $userId = $decodedHeader->data->userId;
            $userRole = $decodedHeader->data->userType;
            
            $appointmentId = filter_var($userId, FILTER_SANITIZE_STRING);

            $appointment = $this->service->getOne($appointmentId);

            if (!$appointment) {
                $this->respondWithError(404, "Appointment not found");
                return;
            }
    
            if ($userRole !== 'Administrator' && ($userId !== $appointment->studentId && $userId !== $appointment->tutorId)) {
                $this->respondWithError(403, "You do not have permission to access this appointment");
                return;
            }

            $this->respond($appointment);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
        
    }

    public function create()
    {
        try {
            $decodedHeader = $this->verifyToken();
            if(!$decodedHeader){
                return;
            }

            $appointment = $this->createObjectFromPostedJson("Models\\Appointment");
            $validator = new Validation((array)$appointment, ['appointmentDate', 'appointmentTime', 'studentId', 'tutorId', 'status']);
            $errors = $validator->validate();
            if (!$validator->isValid()) {
                $this->respondWithError(400, $errors);
                return;
            }
            $isAvailable = $this->service->checkAppointmentAvailability($appointment->tutorId, $appointment->appointmentDate, $appointment->appointmentTime);
            
            if (!$isAvailable) {
                return $this->respondWithError(400, "Selected appointment time is not available.");
            }
            
            $appointment = $this->service->create($appointment);

            $this->respond($appointment);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function update($appointmentId)
    {
        try {
            $decodedHeader = $this->verifyToken();
            if(!$decodedHeader){
                return;
            }

            $appointment = $this->createObjectFromPostedJson("Models\\Appointment");
            $validator = new Validation((array)$appointment, ['appointmentDate', 'appointmentTime', 'studentId', 'tutorId', 'status']);
            $errors = $validator->validate();
            if (!$validator->isValid()) {
                $this->respondWithError(400, $errors);
                return;
            }
            
            $appointment->appointmentId = $appointmentId;
            $appointment = $this->service->update($appointment);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($appointment);
    }
}
