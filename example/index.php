<?php

include_once 'includes/connection.php';
include_once 'includes/user.php';
include_once 'includes/template.php';


$template_content = get_template_content("index.html");


$user = new User($db);
$errorMsg = '';
$successMsg = '';
$msg ='';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	$action = $_POST['action'];

	switch ($action) {
		case 'register':
			$email = $_POST['registerEmail'];
			$name = $_POST['registerName'];
			$file = $_FILES; // Get the name of the uploaded file
			
			if ( empty($email) || empty ($name) )  {
				$errorMsg ='Register: Name or Email missing ';
			} else {
				$msg = $user->register($name, $email, $file);
				$successMsg = 'User registered Successfully';
			
			}
			break;
		case 'login':
			$email = $_POST['loginEmail'];
			$password =  $_POST['loginPassword'];
			if ( empty($email) || empty ($password) )  {
				$errorMsg ='Login: Email or Password missing ';
			}	else {
				$user->login($name, $email);
				$successMsg = 'User logged in Successfully';
			}
			break;
		default;
	}
    // Validate form data

}

$template_content = str_ireplace('%errorMsg%',$errorMsg, $template_content);
$template_content = str_ireplace('%successMsg%',$successMsg, $template_content);
$template_content = str_ireplace('%msg%',$msg, $template_content);


echo $template_content;


