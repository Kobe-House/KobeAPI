<?php 

function scrapeBestbuy($linkToScrape, $apiKey) {
    $ch = curl_init();

    $options = [
        CURLOPT_URL            => 'https://api.zyte.com/v1/extract',
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST           => 1,
        CURLOPT_ENCODING       => '',
        CURLOPT_POSTFIELDS     => json_encode([
            'url'             => $linkToScrape,
            'browserHtml' => true,
            'actions' => [
                [
                    'action' => 'scrollBottom'
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept-Encoding: gzip',
            'Authorization: Basic ' . base64_encode($apiKey . ':')
        ],
    ];

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        return [];
    }

    curl_close($ch);
    $data = json_decode($response);

    if (!$data || !isset($data->browserHtml)) {
        echo 'Error: Invalid response format';
        return [];
    }

    $dom = new DOMDocument();

    // Load HTML only if it's available and not empty
    if (!empty($data->browserHtml)) {
        libxml_use_internal_errors(true); // Enable libxml errors handling
        $dom->loadHTML($data->browserHtml);
        libxml_clear_errors(); // Clear any libxml errors
    } else {
        echo 'Error: Empty HTML content';
        return [];
    }

    $xpath = new DOMXPath($dom);

    // Updated XPath query to target images with src attribute starting with "productImage"
    $productImageNodes = $xpath->query('//img[starts-with(@class, "productImage_")]');

    $imageLinks = [];

    foreach ($productImageNodes as $imgNode) {
        $imageLink = $imgNode->getAttribute('src');
        $imageLinks[] = $imageLink;
    }

    $imageLinks = array_unique($imageLinks);
    array_shift($imageLinks);

    return $imageLinks;
}

?>