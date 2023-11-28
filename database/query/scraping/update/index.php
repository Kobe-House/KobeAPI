<?php
// Reporting All Errors 
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Setting Headers for Cross Origin Resource Sharing
header("Access-Control-Allow-Origin: http://sellerzone.io");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept");

//Database Connection
include('../../../migrations/2023083000000_scraping_bulk_kobe.php');
$connection = new Connection();
$mysqli = $connection->getConnection();

//Get API contents
$getJson = file_get_contents("php://input", true);
$data = json_decode($getJson);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($data) {

        //Extract the product information from the JSON data
        $productId = $mysqli->real_escape_string($data->dataToSend->productId);
        $productTitle = $mysqli->real_escape_string($data->dataToSend->productTitle);
        $productDescriptions = $data->dataToSend->productDescriptions;

        //Update Product Table
        $updateSql = "UPDATE `product` SET `title` = '$productTitle', `updated_at` = now() WHERE `product_id` = '$productId'";
        $resultUpdate = $mysqli->query($updateSql);

        // Update Description
        foreach ($productDescriptions as $description) {
            $productDescriptionId = $description->productDescriptionId;
            $descriptionName = $mysqli->real_escape_string($description->descriptionName);

            // Check if description already exists
            $checkExistingSql = "SELECT `product_description_id` 
                                FROM `product_description`
                                WHERE `product_id` = '$productId' AND `product_description_id` = '$productDescriptionId'";
            $existingResult = $mysqli->query($checkExistingSql);

            if ($existingResult->num_rows > 0) {

                //update if it exists
                $row = $existingResult->fetch_assoc();
                $descriptionId = $row['product_description_id'];

                $updateDescription = "UPDATE `product_description` 
                                    SET `description_name` = '$descriptionName' 
                                    WHERE `product_description_id` = '$productDescriptionId'";
                $descriptionResult = $mysqli->query($updateDescription);
            } else {

                // Insert one if not exist
                $insertDescription = "INSERT INTO `product_description` (`product_id`, `description_name`) 
                                    VALUES ('$productId', '$descriptionName')";
                $descriptionResult = $mysqli->query($insertDescription);
            }
        }


        //Error Handling
        if (!$resultUpdate && !$descriptionResult) {
            echo json_encode(["Update Error:" => $mysqli->error]);
            exit();
        } else {
            echo json_encode(["Result:" => "Updated Successfully!"]);
        }
    }
}
