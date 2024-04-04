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
            $postedUser->password = $this->service->hashPassword($user->password);
            $user = $this->service->register($user);
        } catch(Exception $e) {
            $this->respondWithError(500, $e->getMessage());
            return;
        }

        $this->respond($user);
    }

    public function update($userId) {
        $decodedJwt = $this->verifyToken();
        if(!($decodedJwt->data->userType == "Administrator" || $decodedJwt->data->id == $userId)) {
            $this->respondWithError(403, "Forbidden - Since you are not an Administrator, you can only update your account.");
            return;
        }

        try {
            $user = $this->createObjectFromPostedJson("Models\\User");
            $user->userId = $userId;
            $updatedUser = $this->service->update($user);
            $this->respond($user);
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
        
        if($decodedJwt->data->userId != $userId || $decodedJwt->data->userType !== "Administrator"){
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
        
        if(!($decodedJwt->data->userType == "Administrator" || $decodedJwt->data->id == $userId)) {
            $this->respondWithError(403, "Forbidden - Since you are not an Administrator, you can only delete your account.");
            return;
        }

        try{
            $result = $this->service->delete($userId);
            if ($result === true) {
                $this->respond(['success' => true]);
            } else {
                $this->respondWithError(500, "Failed to delete user: " . $result->getMessage());
            }
        } catch(Exceptipn $e) {
            $this->respondWithError(500, $e->getMessage());
        }

        $this->respond(['success' => true]);
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
}
