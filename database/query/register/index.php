<?php
//header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Origin: http://sellerzone.io");
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept");
// Handle preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the Db   
include('../../migrations/2023083000000_scraping_bulk_kobe.php');
$connection = new Connection();
$mysqli = $connection->getConnection();

// Show all Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Get the Payload
$json = file_get_contents('php://input', true);
$apiData = json_decode($json);

if ($apiData->email != NULL) {

    $email = $apiData->email;

    if ($apiData->firstName != NULL) {
        $firstName = $apiData->firstName;
    }
    if ($apiData->lastName != NULL) {
        $lastName = $apiData->lastName;
    }
    if ($apiData->userName != NULL) {
        $userName = $apiData->userName;
    }

    if ($apiData->confirmPassWord != NULL) {
        $confirmPassWord = $apiData->confirmPassWord;
    }
    if ($apiData->passWord != NULL) {
        $passWord = $apiData->passWord;
    }

    //Check if the user exist
    $sqlCheckUser = " SELECT user_id FROM `users` c WHERE c.`email` = '$email'";
    $result = $mysqli->query($sqlCheckUser);

    if ($result->num_rows > 0) {
        echo json_encode(["Email Exist" => "Email Already exists"]);
        exit;
    } else {

        //New Account Registration

        //	Check if Passwords match
        if ($passWord != $confirmPassWord) {
            echo json_encode(["Password Mismatch" => "Password does not match confirm password"]);
            exit;
        } else {

            //Encrypt the password
            $cost = 10;
            $salt = strtr(base64_encode(random_bytes(16)), '+', '.');
            $salt = sprintf("$2a$%02d$", $cost) . $salt;
            $hash = crypt($passWord, $salt);

            function GUIDv4()
            {
                if (function_exists('openssl_random_pseudo_bytes') === true) {
                    $data = openssl_random_pseudo_bytes(16);
                    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
                    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
                    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
                }
                return 'N/A';
            }

            $userGuid = GUIDv4();

            // Get User's IP Address & Guid
            $userIP = $_SERVER['REMOTE_ADDR'];

            // Insert user info
            $registrationInsert = "INSERT INTO `users`
                 (`username`, `password`, `email`, `access_level`, `firstname`, `lastname`, `ip`, `user_guid`, `active`, `created_at`)
                 VALUES ('$userName ', '$hash', '$email', 0, '$firstName', '$lastName', '$userIP', '$userGuid', 0, now())";
            $queryResult = $mysqli->query($registrationInsert);

            if (!$queryResult) {
                printf("%s\n", $mysqli->error);
                exit();
            } else {
                //Send Email to Notify Registration Confirmation
                // $fromtext = "info@kobewarehouse.com";
                // $headers = "MIME-Version: 1.0\n";
                // $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                // $headers .= "From: " . $fromtext . "\n";

                // $subject = "Registration Confirmation";
                // $body = "<html>
                //             <head>
                //                 <style>
                //                 body {
                //                     font-family: 'Arial', sans-serif;
                //                     text-align: center;
                //                 }
                //                 h1 {
                //                     font-size: 20px;
                //                 }
                //                 p {
                //                     font-size: 13px;
                //                 }
                //                 </style>
                //             </head>
                //             <body>
                //                 <h1>Dear " . $firstName . ",</h1>
                //                 <p>This is to confirm that your account has been created successfully.</p>
                //                 <p>Find the link below to <strong>LOGIN</strong> into your account:</p>
                //                 <p>- Link: <a href='http://sellerzone.io/#/login/' style='font-weight: bold;'>http://sellerzone.io/#/login/</a></p>
                //                 <p><strong>NB:</strong> Use Your Email To Login.</p>
                //                 <p>Best Regards,<br>Kobe Warehouse Canada</p>
                //             </body>
                //         </html>";

                // $recipient = "$email";
                // $emailSent = mail($recipient, $subject, $body, $headers);
                echo json_encode(["Success Registration" => "Registration Successfully"]);
            }
            // if ($emailSent) {
            //     echo json_encode(["Success Registration" => "Registration Successfully"]);
            // } else {
            //     echo json_encode(["Email Msg" => "Email Not sent"]);
            // }
        }
    }
} else {
    echo json_encode(["Enter Email" => "Email is required"]);
}
