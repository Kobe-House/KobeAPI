<?php

// Reporting All Errors 
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Setting Headers for Cross Origin Resource Sharing
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Accept");

//Database Connection
include('../../../migrations/2023083000000_scraping_bulk_kobe.php');
$connection = new Connection();
$mysqli = $connection->getConnection();

//Get the Scraped Data
//$sql = "SELECT * FROM `product`";
$sql = "SELECT p.product_id, p.created_at, p.title, p.image_url, pd.description_name, pd.product_description_id
        FROM product p
        LEFT JOIN product_description pd
        ON p.product_id = pd.product_id
        ORDER BY p.created_at DESC ";

$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode(["Error:" => $mysqli->error]);
} else {
    // Initialize an array to hold fetched data
    $products = [];

    if ($result->num_rows > 0) {
        while ($data = $result->fetch_assoc()) {
            $productId = trim($data['product_id']);
            $productTitle = trim($data['title']);
            $imageURL = stripslashes($data['image_url']);
            $productDescription = [
                'productDescriptionId' => trim($data['product_description_id']),
                'descriptionName' => $data['description_name']
            ];
    

            // Check if the product exists in the array and append the description
            $found = false;
            foreach ($products as &$product) {
                if ($product["productTitle"] === $productTitle) {
                    $product["productDescriptions"][] = $productDescription;
                    $found = true;
                    break;
                }
            }
            // If the product doesn't exist in the array, create a new entry
            if (!$found) {
                $newProduct = [
                    'productId' => $productId,
                    'productTitle' => $productTitle,
                    'imageURL' => $imageURL,
                    'productDescriptions' => [$productDescription]
                ];
                $products[] = $newProduct;
            }
        }
    } else {
        echo json_encode(["Message" => "There's Nothing"]);
    }

    echo json_encode($products);
}

?>