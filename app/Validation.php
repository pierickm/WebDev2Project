<?php
class Validation {
    private $data;
    private $errors = [];
    private $requiredFields = [];
    private $emailFields = [];
    private $allowedUserTypes = ['Administrator', 'Tutor', 'Student'];


    public function __construct($postData, $requiredFields = [], $emailFields = []) {
        $this->data = $postData;
        $this->requiredFields = $requiredFields;
        $this->emailFields = $emailFields;
    }

    public function validate() {
        $this->validateRequiredFields();
        $this->validateEmailFormat();
        return $this->getErrors();
    }

    private function validateRequiredFields() {
        foreach ($this->requiredFields as $field) {
            if (empty($this->data[$field])) {
                $this->errors[] = "{$field} is required.";
            }
        }
    }

    private function validateEmailFormat() {
        foreach ($this->emailFields as $field) {
            if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = "Invalid email format for {$field}.";
            }
        }
    }

    private function validateUserType() {
        $userType = $this->data['userType'] ?? null;
        if (!$userType || !in_array($userType, $this->allowedUserTypes)) {
            $this->errors[] = "Invalid user type. Allowed types are: " . implode(", ", $this->allowedUserTypes);
        }
    }

    public function getErrors() {
        return implode(" ", $this->errors); 
    }

    public function isValid() {
        return empty($this->errors);
    }
}
