<?php

namespace Controllers;

require __DIR__ . '/../vendor/autoload.php';
use Exception;
use Models\User;
use Services\UserService;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class UserController extends Controller
{
    private $service;

    // initialize services
    function __construct()
    {
        parent::__construct();
        $this->service = new UserService();
    }

    public function login() {
        $postedUser = $this->createObjectFromPostedJson("Models\\User");
        $user = $this->service->CheckLogin($postedUser->password, $postedUser->emailAddress);
        
        if(!$user) {
            $this->respondWithError(401, "Invalid login");
            return;
        }

        try{
            $tokenResponse = $this->generateJWT($user);
            $this->respond($tokenResponse);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function register() {
        try{
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
            $postedUser->password = $this->service->hashPassword($postedUser->password);
            $user = $this->service->register($postedUser);
        } catch(Exception $e) {
            $this->respondWithError(500, $e->getMessage());
            return;
        }

        $this->respond($user);
    }


    public function create() {
        try{
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
            $postedUser->password = $this->service->hashPassword($postedUser->password);
            $user = $this->service->create($postedUser);
        } catch(Exception $e) {
            $this->respondWithError(500, $e->getMessage());
            return;
        }

        $this->respond($user);
    }

    public function update($userId) {
        $decodedJwt = $this->verifyToken();
        try {
            $user = $this->createObjectFromPostedJson("Models\\User");
            if(!$decodedJwt->data->userType == "Administrator" && !$decodedJwt->data->userId == $userId) {
                $this->respondWithError(403, "Forbidden - Since you are not an Administrator, you can only update your account.");
                return;
            }
            $user->userId = $userId;
            $updatedUser = $this->service->update($user);
            if($updatedUser && $user->deleteTutorEntry) {
                $this->service->deleteTutorEntry($user->userId);
            }
            $this->respond($updatedUser);
        } catch(Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAll(){
        try{
            $decodedJwt = $this->verifyToken();
            if(!$decodedJwt){
                return;
            }
            
            if(!$decodedJwt->data->userType == "Administrator") {
                $this->respondWithError(403, "Unauthorized access. Only administrators have access to this.");
                return;
            }

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] :20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] :0;

            $users = $this->service->getAll($limit, $offset);
            $this->respond($users);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getOne($userId) {
        $decodedJwt = $this->verifyToken();
        if($decodedJwt->data->userId != $userId && $decodedJwt->data->userType !== "Administrator"){
            $this->respondWithError(403, "Forbidden - Since you are not an Administrator, you can only view your own account.");
            return;
        }

        try {
            $user = $this->service->getOne($userId);
            if($user) {
                $this->respond($user);
            } else {
                $this->respondWithError(404, "User not found");
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function delete($userId) {
        $decodedJwt = $this->verifyToken();
        
        if(!$decodedJwt->data->userType == "Administrator") {
            $this->respondWithError(403, "Forbidden - Only administrators can delete accounts.");
            return;
        }

        try{
            $result = $this->service->delete($userId);
            if ($result === true) {
                $this->respond(['success' => true]);
            } else {
                $this->respondWithError(500, "Failed to delete user: " . $result);
            }
        } catch(Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function generateJWT($user) {
        $issuedAt = time();
        $expirationSpan = $issuedAt + (30 * 60);
        
        $token = JWT::encode([
            "iss" => 'localhost.com',
            "aud" => 'localhost.com',
            "iat" => $issuedAt,
            "nbf" => $issuedAt,
            "exp" => $expirationSpan,
            "data" => [
                "userId" => $user->userId,
                "emailAddress" => $user->emailAddress,
                "userType" => $user->userType
            ]
        ], $this->secretJwt, 'HS256');

        return array(
            "message" => "Successful login.",
            "token" => $token,
            "expireAt" => $expirationSpan
        );
    }

    public function uploadProfilePhoto() {
    
        $decodedJwt = $this->verifyToken();
        if(!$decodedJwt){
            return;
        }
    
        if (isset($_FILES['profilePhoto'])) {
            $file = $_FILES['profilePhoto'];
            $directory = __DIR__ . '/../public/uploads/';
            $targetFile = $directory . basename($file['name']);
            
            // You may want to add file validation here (e.g., check file type, size)
    
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $filePath = '/uploads/' . basename($file['name']);
    
                $this->respond(['success' => true, 'filePath' => $filePath]);
            } else {
                $this->respondWithError(500, "Failed to upload file.");
            }
        } else {
            $this->respondWithError(400, "No file was uploaded.");
        }
    }
}
