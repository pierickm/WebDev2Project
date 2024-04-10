<?php

namespace Controllers;

use Exception;
use Services\AppointmentService;

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
        try{
            
            $decodedHeader = $this->verifyToken();
            if(!$decodedHeader){
                return;
            }

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] :20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] :0;

            if($decodedHeader->data->userType == 'Administrator'){
                $appointments = $this->service->getAll($offset, $limit);
                $this->respond($appointments);
            }
            
            $userId = $decodedHeader->data->userId;
            $appointments = $this->service->getAll($offset, $limit, $userId);
    
            $this->respond($appointments);
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
            $appointment->appointmentId = $appointmentId;
            $appointment = $this->service->update($appointment);

        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond($appointment);
    }

    public function delete($appointmentId)
    {
        try {
            $decodedHeader = $this->verifyToken();
            if(!$decodedHeader){
                return;
            }

            if ($userRole !== 'Administrator') {
                $this->respondWithError(403, "You do not have permission to delete this permission. You can only cancel it.");
                return;
            }

            $this->service->delete($AppointmentId);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(true);
    }

}
