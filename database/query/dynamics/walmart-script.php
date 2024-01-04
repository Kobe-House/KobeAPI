<?php

function scrapeWalmart($linkToScrape, $apiKey)
{
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

    $scrappedData = [
        'size'   => "N/A",
        'colour' => "N/A",
        'sku'    => "N/A",
        'upc'    => "N/A",
    ];
    $walmartDescription = array();

    $xpath = new DOMXPath($dom);

    // Retrieve specific elements directly using XPath queries
    $size   = $xpath->query("//h3[text()='Size']/following-sibling::div/span/text()");
    $colour = $xpath->query("//h3[text()='Colour']/following-sibling::div/span/text()");
    $sku    = $xpath->query("//h3[text()='SKU']/following-sibling::div/span/text()");
    $upc    = $xpath->query("//h3[text()='Universal Product Code (UPC check)']/following-sibling::div/span/text()");
    $details = $xpath->query("//div[starts-with(@class, 'dangerous-html')]");

    // Assign values if elements exist
    if ($size->length > 0) {
        $scrappedData['size'] = $size->item(0)->nodeValue;
    }
    if ($colour->length > 0) {
        $scrappedData['colour'] = $colour->item(0)->nodeValue;
    }
    if ($sku->length > 0) {
        $scrappedData['sku'] = $sku->item(0)->nodeValue;
    }
    if ($upc->length > 0) {
        $scrappedData['upc'] = $upc->item(0)->nodeValue;
    }
    if ($details->length > 0) {
        $d = $details->item(1);
        $dHTML = $dom->saveHTML($d);
        $dHTML = str_replace('• ', '', $dHTML);
        $dHTML = str_replace('<ul>', '', $dHTML);
        $dHTML = str_replace('</ul>', '', $dHTML);
        $dHTML = str_replace('<li>', '<br>', $dHTML);
        $dHTML = str_replace('</li>', '', $dHTML);
        $dHTML = str_replace('<b>', '', $dHTML);
        $dHTML = str_replace('</b>', '', $dHTML);
        $dHTML = str_replace('<p>', '', $dHTML);
        $dHTML = str_replace('</p>', '', $dHTML);
        $walmartDescription[] = explode('<br>', $dHTML);
    }

    return ['scrappedData' => $scrappedData, 'walmartDescription' => $walmartDescription];
}
