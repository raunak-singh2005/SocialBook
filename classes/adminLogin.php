<?php

// AdminLogin class handles admin authentication and registration
class AdminLogin {
    // Stores error messages
    private $error = "";

    // Public method to evaluate admin login credentials
    public function evaluate($data) {
        // Sanitize input
        $email = addslashes($data['email']);
        $password = addslashes($data['password']);
        $hashedPassword = $this->hashPassword($password);

        // Query to check if admin exists with given credentials
        $query = "SELECT * FROM Admins WHERE email = '$email' AND password = '$hashedPassword' LIMIT 1";
        $DB = new Database();
        $result = $DB->read($query);

        if ($result) {
            // Successful login, set session and redirect
            $row = $result[0];
            $_SESSION['SocialBook_adminID'] = $row['adminID'];
            header("Location: adminPage.php");
            die;
        } else {
            // Invalid credentials
            $this->error = "Invalid admin email or password";
        }
        return $this->error;
    }

    // Public method to create a new admin
    public function createAdmin($data) {
        // Sanitize and format input
        $firstName = ucfirst(addslashes($data['firstName']));
        $lastName = ucfirst(addslashes($data['lastName']));
        $email = addslashes($data['email']);
        $password = $this->hashPassword($data['password']);
        $adminID = $this->createAdminID();
        $date = date("Y-m-d H:i:s");

        // Insert new admin into database
        $DB = new Database();
        $query = "INSERT INTO Admins (adminID, email, password, firstName, lastName, date) VALUES ('$adminID', '$email', '$password', '$firstName', '$lastName', '$date')";
        $DB->write($query);
    }

    // Private method to hash passwords using SHA-256
    private function hashPassword($password) {
        return hash('sha256', $password);
    }

    // Private method to generate a random admin ID
    private function createAdminID() {
        $length = rand(6, 20);
        $number = "";
        for ($i = 0; $i < $length; $i++) {
            $number .= rand(0, 9);
        }
        return $number;
    }
}