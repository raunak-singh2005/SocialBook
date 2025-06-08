<?php

// Signup class handles user registration logic
class Signup {
    // Stores error messages
    private $error = "";

    /**
     * Evaluates the signup data for errors and creates a user if valid.
     */
    public function evaluate($data)
    {
        $errors = "";

        // Validate each field in the input data
        foreach ($data as $key => $value) {
            // Check for empty fields
            if (empty($value)) {
                $errors .= $key . " is empty!<br>";
            }

            // Validate email format
            if ($key == "email") {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors .= "Email is not valid!<br>";
                }
            }

            // Validate first and last name (should not be numeric or contain spaces)
            if ($key == "firstName" || $key == "lastName") {
                if (is_numeric($value) || strstr($value, " ")) {
                    $errors .= $key . " first or last name should not be a number or have spaces!<br>";
                }
            }
        }

        // Check if email already exists in the database
        $DB = new Database();
        $email = addslashes($data['email']);
        $result = $DB->read("SELECT userid FROM Users WHERE email = '$email' LIMIT 1");
        if ($result) {
            $errors .= "That email is already registered.<br>";
        }

        // If no errors, create the user; otherwise, return errors
        if ($errors == "") {
            $this->createUser($data);
        } else { 
            return $errors;
        }
    }

    /**
     * Creates a new user in the database.
     */
    public function createUser($data) {
        $userID = $this->createUserID();
        $firstName = ucfirst($data['firstName']);
        $lastName = ucfirst($data['lastName']);
        $gender = $data['gender'];
        $email = $data['email'];
        $password = hash('sha256', $data['password']); // Hash the password
        $profileURL = strtolower($firstName . "." . $lastName);

        $query = "INSERT INTO Users (userid, firstName, lastName, gender, email, password, profileURL) 
                  VALUES ('$userID', '$firstName', '$lastName', '$gender', '$email', '$password', '$profileURL')";

        $DB = new Database();
        $DB->write($query);
    }

    /**
     * Generates a random user ID.
     */
    private function createUserID() {
        $length = rand(4, 19);
        $number = "";
        for ($i = 0; $i < $length; $i++) {
            $number .= rand(0, 9);
        }
        return $number;
    }
}