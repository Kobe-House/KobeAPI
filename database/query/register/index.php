<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

//Show all Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);	

//Include the Db
include('../../migrations/2023083000000_scraping_bulk_kobe.php');
$exec = new Connection();

//f(X) to generate user guid
function generateGuid() {
    $data = openssl_random_pseudo_bytes(16);
    $guid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    return $guid;
  }
 
//----Get Posted Data-----
if($_SERVER["REQUEST_METHOD"] == "POST"){

    //Response Array
    $errors = array();

    //Assign Variables
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordComfirm = $_POST['passwordComfirm'];

    //-----Validations-----

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
    if ($password !== $passwordComfirm) {
        $errors["password"] = "Passwords do not match.";
    }

    //Checking If there's no Errors
    if(empty($errors)){

        //Encrpt Password

        $cost = 10;
        $salt = strtr(base64_encode(random_bytes(16)), '+', '.');
        $salt = sprintf("$2a$%02d$", $cost) . $salt;
        $hash = crypt($password, $salt);

    //Check If the Email Exists.......

    //Get User's IP Address & Guid
    $userIP = $_SERVER['REMOTE_ADDR'];
    $userGuid = generateGuid();
    //Insert Into Db

    $registrationInsert = "INSERT INTO `users`
                        (`username`, `password`, `email`, `access_level`, `firstname`, `lastname`, `ip`, `user_guid`, `active`, `created_at`)
                        VALUES ('$username ', '$hash', '$email', 0, '$firstName', '$lastName', '$userIP', '$userGuid', 0, now())";
    $queryResult = $mysqli->query($queryResult);

    if(!$queryResult){
        printf("%s\n", $mysqli->error);
		exit();
    }else{
        //Send Email to Notify Registration Comfirmation
        $fromtext = "info@kobewarehouse.com";
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " .$fromtext. "\r\n";

        $subject = "Registration Confirmation";

        $body = "Dear ".$username.",<br><br>";
        $body .= "This is to confirm that your account has be created successfully, <br>Find the link below to LOGIN into your account:<br>";
        $body .= "- Link: <b>http://localhost:3000/#/logi/<br><br>";
        $body .= "Best Regards,<br> Kobe Warehouse Canada";
        
        $recipient = "$email";	
        mail($recipient,$subject,$body,$headers); 

        echo ("Registration Successfully!");
    }
    }else{
        //Send Errors
        echo json_encode($errors);
    }
    
}
?>