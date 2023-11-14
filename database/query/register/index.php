<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the Db
include('../../../migrations/2023083000000_scraping_bulk_kobe.php');
$connection = new Connection();
$mysqli = $connection->getConnection();

// Show all Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$json = file_get_contents('php://input', true);
$apiData = json_decode($json);
echo json_encode($apiData);

// Get User's IP Address & Guid
$userIP = $_SERVER['REMOTE_ADDR'];

// Uncomment and correct the SQL query
// $registrationInsert = "INSERT INTO `users`
//                         (`username`, `password`, `email`, `access_level`, `firstname`, `lastname`, `ip`, `user_guid`, `active`, `created_at`)
//                         VALUES ('$username ', '$hash', '$email', 0, '$firstName', '$lastName', '$userIP', '$userGuid', 0, now())";

// Uncomment and correct this line
// $queryResult = $mysqli->query($registrationInsert);

// if (!$queryResult) {
//     printf("%s\n", $mysqli->error);
//     exit();
// } else {
//     // Send Email to Notify Registration Confirmation
//     $fromtext = "info@kobewarehouse.com";
//     $headers = "MIME-Version: 1.0\n";
//     $headers .= "Content-type:text/html;charset=UTF-8\n";
//     $headers .= "From: " . $fromtext . "\n";

//     $subject = "Registration Confirmation";

//     $body = "Dear " . $username . ",<br><br>";
//     $body .= "This is to confirm that your account has be created successfully, <br>Find the link below to LOGIN into your account:<br>";
//     $body .= "- Link: <b>http://localhost:3000/#/logi/<br><br>";
//     $body .= "Best Regards,<br> Kobe Warehouse Canada";

//     $recipient = "$email";
//     mail($recipient, $subject, $body, $headers);

//     echo ("Registration Successfully!");
// }
?>
