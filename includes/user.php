<?php

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($name, $email) {
        $verification_code = $this->generateVerificationCode();
        $hashed_password = password_hash($verification_code, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, verification_code) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $verification_code);
        $stmt->execute();
        $stmt->close();

        // Send email with verification code
        $this->sendVerificationEmail($email, $verification_code);
    }

    public function login($email, $verification_code, $password, $confirm_password) {
        // Check if user exists
        $stmt = $this->db->prepare("SELECT id, name, password FROM users WHERE email = ? AND verification_code = ?");
        $stmt->bind_param("ss", $email, $verification_code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $name, $hashed_password);
            $stmt->fetch();
            
            // Verify password and confirm password match
            if (password_verify($password, $hashed_password) && $password === $confirm_password) {
                // Update user password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $id);
                $stmt->execute();
                $stmt->close();
                return true;
            }
        }
        return false;
    }

    private function generateVerificationCode() {
        return bin2hex(random_bytes(16));
    }

    private function sendVerificationEmail($email, $verification_code) {
        // You can implement email sending functionality here
        // Example:
        
        $to = $email;
        $subject = "Verification Code";
        $message = "Your verification code is: $verification_code";
        $headers = "From: your@example.com" . "\r\n" .
            "Reply-To: your@example.com" . "\r\n" .
            "X-Mailer: PHP/" . phpversion();

        mail($to, $subject, $message, $headers);
       
    }
}