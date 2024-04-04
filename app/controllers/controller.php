<?php

namespace Controllers;

use Exception;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class Controller
{
    protected $secretJwt;

    function __construct()
    {
        $this->secretJwt = 'b9c2d8a5f0e3b7d8c6a1d9b3c5a8e6f2d1a4b7c9e8f2d0a1b5c7e2f3d9a5b';
    }

    function verifyToken()
    {
        if(!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->respondWithError(401, "No token was given.");
            return null;
        }

        //get token 
        $authenticationHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $subParts = explode(" ", $authenticationHeader);

        //check header format
        if(count($subParts) !== 2 || $subParts[0] !== 'Bearer') {
            $this->respondWithError(401, "The token format is not valid");
            return null;
        }

        $jwt = $subParts[1];

        try {
            $decodedToken = JWT::decode($jwt, new Key($this->secretJwt, 'HS256'));
            return $decodedToken;
        } catch(Exception $e) {
            $this->respondWithError(401, $e->getMessage());
            return null;
        }

    }

    function respond($data)
    {
        $this->respondWithCode(200, $data);
    }

    function respondWithError($httpcode, $message)
    {
        $data = array('errorMessage' => $message);
        $this->respondWithCode($httpcode, $data);
    }

    private function respondWithCode($httpcode, $data)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpcode);
        echo json_encode($data);
    }

    function createObjectFromPostedJson($className)
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json);


        $object = new $className();
        foreach ($data as $key => $value) {
            if(is_object($value)) {
                continue;
            }
            $object->{$key} = $value;
        }
        return $object;
    }
}
