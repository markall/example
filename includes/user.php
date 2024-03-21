<?php

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

	/**
	*	function register
	*	@param string name, string email, files
	*	@return msg
	*/
	
	public function register($name, $email, $files) {
		$verification_code = $this->generateVerificationCode();
		$hashed_password = password_hash($verification_code, PASSWORD_DEFAULT);
		$msg = '';
		
		// File upload handling
		$target_dir = "uploads/";
		$target_file = $target_dir . date("Y-m-d H:i:s").'_' . basename($files["file"]["name"]);
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

		// Check if file is an actual image
		if(isset($_POST["submit"])) {
			$check = getimagesize($files["file"]["tmp_name"]);
			if($check !== false) {
				$msg = "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			} else {
				$msg =  "File is not an image.";
				$uploadOk = 0;
			}
		}

		// Check if file already exists
		if (file_exists($target_file)) {
			$msg  = "Sorry, file already exists.";
			$uploadOk = 0;
		}

		// Check file size
		if ($_FILES["file"]["size"] > 500000) {
			$msg = "Sorry, your file is too large.";
			$uploadOk = 0;
		}

		// Allow only certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
			$msg =  "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			$uploadOk = 0;
		}

		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			$msg = "Sorry, your file was not uploaded.";
		// If everything is ok, try to upload file
		} else {
			if (move_uploaded_file($files["file"]["tmp_name"], $target_file)) {
				$msg =  "The file ". htmlspecialchars( basename( $files["file"]["name"])). " has been uploaded.";
			} else {
				$msg = "Sorry, there was an error uploading your file.";
			}
		}

		$stmt = $this->db->prepare("INSERT INTO users (name, email, password, verification_code, file) VALUES (?, ?, ?, ?, ?)");
		$stmt->bind_param("sssss", $name, $email, $hashed_password, $verification_code, $target_file);
		$stmt->execute();
		$stmt->close();

		// Send email with verification code
		$this->sendVerificationEmail($email, $verification_code);
		
		return $msg;
	}
	

	/**
		*	function login 
		*	@param string email, string password
		*	@return user
		*
	**/
       public function login($email, $password) {
        // Check if user exists
        $stmt = $this->db->prepare("SELECT id, name, password,file,verification_code FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && ( (password_verify($password, $user['password']))  || ($user['verification_code']==$password) ) ) {
            return $user;
        }

        return false;
    }

	/**
	*	function generateVerificationCode
	*	
	*	@return code
	*/
    private function generateVerificationCode() {
        return bin2hex(random_bytes(8));
    }
	
	
	/**
	*	function sendVerificationEmail
	*.  @params string email, string verification_code
	*	
	*	@return code
	*/
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