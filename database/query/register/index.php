<?php
//Show all Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);	

//Include the Db
include('../../migrations/2023083000000_scraping_bulk_kobe.php');

if($_SERVER["REQUEST_METHOD"] == "POST"){

    //Response Array
    $errors = array();

    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordComfirm = $_POST['passwordComfirm'];

    //Validations....

    //First Name
    if (empty($firstName)) {
        $errors["firstName"] = "First Name is required.";
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $firstName)) {
        $errors["firstName"] = "First Name should contain only letters and spaces.";
    }

    //Last Name
    if (empty($lastName)) {
        $errors["lastName"] = "Last Name is required.";
    } elseif (!preg_match('/^[A-Za-z\s]+$/', $lastName)) {
        $errors["lastName"] = "Last Name should contain only letters and spaces.";
    }

    //Username
    if (!preg_match("/^[a-zA-Z0-9]{3,20}$/", $username)) {
        // Invalid username
        $errors["username"] = "Username must be 3 to 20 characters and contain only letters and numbers.";
    }

    //Passowrd 
    if (strlen($password) < 8 ||
        !preg_match("/[a-z]/", $password) ||
        !preg_match("/[A-Z]/", $password) ||
        !preg_match("/[0-9]/", $password)) {
        // Invalid password
        $errors["password"] = "Password must be at least 8 characters and include at least one uppercase letter, one lowercase letter, and one number.";
    }
    if (strlen($passwordComfirm) < 8 ||
        !preg_match("/[a-z]/", $passwordComfirm) ||
        !preg_match("/[A-Z]/", $passwordComfirm) ||
        !preg_match("/[0-9]/", $passwordComfirm)) {
        // Invalid password
        $errors["password"] = "Password must be at least 8 characters and include at least one uppercase letter, one lowercase letter, and one number.";
    }

    //Check if passwords match
    if ($password !== $passwordConfirm) {
        $errors["password"] = "Passwords do not match.";
    }

    //Encrpt Password

	// A higher "cost" is more secure but consumes more processing power
	$cost = 10;
	// Create a random salt
	$salt = strtr(base64_encode(random_bytes(16)), '+', '.');
	$salt = sprintf("$2a$%02d$", $cost) . $salt;

	// Hash the password with the salt
	$hash = crypt($password, $salt);

    //Check If the Email Exists.......

    //Get User's IP Address
    $userIP = $_SERVER['REMOTE_ADDR'];

    echo json_encode($errors);


}


?>