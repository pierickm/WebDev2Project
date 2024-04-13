<?php

namespace Controllers;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Validation.php';

use Exception;
use Models\User;
use Services\UserService;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Validation;

class UserController extends Controller
{
    private $service;

    function __construct()
    {
        parent::__construct();
        $this->service = new UserService();
    }

    public function login()
    {
        $postedUser = $this->createObjectFromPostedJson("Models\\User");

        $validator = new Validation((array) $postedUser, [], ['emailAddress']);
        $errors = $validator->validate();
        if (!$validator->isValid()) {
            $this->respondWithError(400, $errors);
            return;
        }
        $user = $this->service->CheckLogin($postedUser->password, $postedUser->emailAddress);

        if (!$user) {
            $this->respondWithError(401, "Invalid login");
            return;
        }

        try {
            $tokenResponse = $this->generateJWT($user);
            $this->respond($tokenResponse);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function register()
    {
        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
            // Validation
            $validator = new Validation((array) $postedUser, ['emailAddress', 'password', 'firstName', 'lastName'], ['emailAddress']);
            $errors = $validator->validate();
            if (!$validator->isValid()) {
                $this->respondWithError(400, $errors);
                return;
            }

            $postedUser->password = $this->service->hashPassword($postedUser->password);
            $user = $this->service->register($postedUser);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
            return;
        }

        $this->respond($user);
    }


    public function create()
    {
        try {
            $postedUser = $this->createObjectFromPostedJson("Models\\User");
            $validator = new Validation((array) $postedUser, ['emailAddress', 'password', 'firstName', 'lastName', 'userType'], ['emailAddress']);
            $errors = $validator->validate();
            if (!$validator->isValid()) {
                $this->respondWithError(400, $errors);
                return;
            }
            $postedUser->password = $this->service->hashPassword($postedUser->password);
            $user = $this->service->create($postedUser);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
            return;
        }

        $this->respond($user);
    }

    public function update($userId)
    {
        $decodedJwt = $this->verifyToken();
        try {
            $user = $this->createObjectFromPostedJson("Models\\User");
            $validator = new Validation((array) $user, ['firstName', 'lastName', 'emailAddress', 'userType'], ['emailAddress']);
            $errors = $validator->validate();
            if (!$validator->isValid()) {
                $this->respondWithError(400, $errors);
                return;
            }
            if (!$decodedJwt->data->userType == "Administrator" && !$decodedJwt->data->userId == $userId) {
                $this->respondWithError(403, "Forbidden - Since you are not an Administrator, you can only update your account.");
                return;
            }
            $user->userId = $userId;
            $updatedUser = $this->service->update($user);
            if ($updatedUser && isset($user->deleteTutorEntry) && $user->deleteTutorEntry) {
                $this->service->deleteTutorEntry($user->userId);
            }
            $this->respond($updatedUser);
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function getAll()
    {
        try {
            $decodedJwt = $this->verifyToken();
            if (!$decodedJwt) {
                return;
            }

            if (!$decodedJwt->data->userType == "Administrator") {
                $this->respondWithError(403, "Unauthorized access. Only administrators have access to this.");
                return;
            }

            $limit = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) : 20;
            $offset = isset($_GET['offset']) ? filter_var($_GET['offset'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]) : 0;

            $users = $this->service->getAll($limit, $offset);
            $total = $this->service->getTotalUsersCount();

            $response = [
                'data' => $users,
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
        $decodedJwt = $this->verifyToken();
        if ($decodedJwt->data->userId != $userId && $decodedJwt->data->userType !== "Administrator") {
            $this->respondWithError(403, "Forbidden - Since you are not an Administrator, you can only view your own account.");
            return;
        }

        $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);

        try {
            $user = $this->service->getOne($userId);
            if ($user) {
                $this->respond($user);
            } else {
                $this->respondWithError(404, "User not found");
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function delete($userId)
    {
        $decodedJwt = $this->verifyToken();

        if (!$decodedJwt->data->userType == "Administrator") {
            $this->respondWithError(403, "Forbidden - Only administrators can delete accounts.");
            return;
        }

        $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);

        try {
            $result = $this->service->delete($userId);
            if ($result === true) {
                $this->respond(['success' => true]);
            } else {
                $this->respondWithError(500, "Failed to delete user: " . $result);
            }
        } catch (Exception $e) {
            $this->respondWithError(500, $e->getMessage());
        }
    }

    public function generateJWT($user)
    {
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

    public function uploadProfilePhoto()
    {

        $decodedJwt = $this->verifyToken();
        if (!$decodedJwt) {
            return;
        }

        if (isset($_FILES['profilePhoto'])) {
            $file = $_FILES['profilePhoto'];
            if (!$this->validateFile($file)) {
                return;
            }
            $directory = __DIR__ . '/../public/uploads/';
            $filename = $this->sanitizeFilename($file['name']);
            $targetFile = $directory . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $filePath = '/uploads/' . $filename;

                $this->respond(['success' => true, 'filePath' => $filePath]);
            } else {
                $this->respondWithError(500, "Failed to upload file.");
            }
        } else {
            $this->respondWithError(400, "No file was uploaded.");
        }
    }
    private function validateFile($file)
    {
        // Validate file size
        $maxFileSize = 5 * 1024 * 1024; // 5 MB in bytes
        if ($file['size'] > $maxFileSize) {
            $this->respondWithError(400, "File size exceeds the maximum limit of 5MB.");
            return false;
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            $this->respondWithError(400, "Invalid file type. Only JPEG, PNG, and GIF are allowed.");
            return false;
        }

        return true;
    }

    private function sanitizeFilename($filename)
    {
        // Remove potentially harmful characters
        $safeFilename = preg_replace("/[^a-zA-Z0-9.]+/", "", basename($filename));
        return $safeFilename;
    }
}
