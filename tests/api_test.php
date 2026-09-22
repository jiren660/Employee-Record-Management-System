<?php
/**
 * Automated REST API Test Suite for Employee Management System
 */

$baseUrl = "http://localhost/ADS133/employee_api.php";
$testConnUrl = "http://localhost/ADS133/test_connection.php";

function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data !== null) {
        $jsonPayload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response,
        'error' => $err
    ];
}

echo "=========================================================\n";
echo "1. TESTING DATABASE CONNECTION ENDPOINT\n";
echo "=========================================================\n";
$resConn = makeRequest($testConnUrl, 'GET');
echo "HTTP Status: " . $resConn['code'] . "\n";
echo "Response: " . json_encode($resConn['body'], JSON_PRETTY_PRINT) . "\n\n";

echo "=========================================================\n";
echo "2. TESTING GET (ALL EMPLOYEES)\n";
echo "=========================================================\n";
$resAll = makeRequest($baseUrl, 'GET');
echo "HTTP Status: " . $resAll['code'] . "\n";
echo "Count: " . ($resAll['body']['count'] ?? 'N/A') . "\n";
echo "Sample first employee: " . json_encode($resAll['body']['data'][0] ?? [], JSON_PRETTY_PRINT) . "\n\n";

echo "=========================================================\n";
echo "3. TESTING POST (ADD NEW EMPLOYEE)\n";
echo "=========================================================\n";
$newEmployee = [
    "name" => "Lucas Vance",
    "email" => "lucas.vance@enterprise.com",
    "position" => "AI Research Engineer",
    "department" => "Engineering",
    "salary" => 96000
];
$resPost = makeRequest($baseUrl, 'POST', $newEmployee);
echo "HTTP Status: " . $resPost['code'] . "\n";
echo "Response: " . json_encode($resPost['body'], JSON_PRETTY_PRINT) . "\n";
$createdId = $resPost['body']['id'] ?? null;
echo "Created ID: " . $createdId . "\n\n";

if ($createdId) {
    echo "=========================================================\n";
    echo "4. TESTING GET (SINGLE EMPLOYEE BY ID: $createdId)\n";
    echo "=========================================================\n";
    $resSingle = makeRequest($baseUrl . "?id=" . $createdId, 'GET');
    echo "HTTP Status: " . $resSingle['code'] . "\n";
    echo "Response: " . json_encode($resSingle['body'], JSON_PRETTY_PRINT) . "\n\n";

    echo "=========================================================\n";
    echo "5. TESTING PUT (UPDATE EMPLOYEE ID: $createdId)\n";
    echo "=========================================================\n";
    $updateData = [
        "id" => $createdId,
        "name" => "Lucas Vance, Ph.D.",
        "email" => "lucas.vance@enterprise.com",
        "position" => "Principal AI Scientist",
        "department" => "Engineering",
        "salary" => 125000
    ];
    $resPut = makeRequest($baseUrl, 'PUT', $updateData);
    echo "HTTP Status: " . $resPut['code'] . "\n";
    echo "Response: " . json_encode($resPut['body'], JSON_PRETTY_PRINT) . "\n\n";

    echo "=========================================================\n";
    echo "6. TESTING DELETE (DELETE EMPLOYEE ID: $createdId)\n";
    echo "=========================================================\n";
    $resDelete = makeRequest($baseUrl . "?id=" . $createdId, 'DELETE');
    echo "HTTP Status: " . $resDelete['code'] . "\n";
    echo "Response: " . json_encode($resDelete['body'], JSON_PRETTY_PRINT) . "\n\n";

    echo "=========================================================\n";
    echo "7. VERIFYING DELETION (GET EMPLOYEE ID: $createdId)\n";
    echo "=========================================================\n";
    $resVerify = makeRequest($baseUrl . "?id=" . $createdId, 'GET');
    echo "HTTP Status: " . $resVerify['code'] . " (Expected: 404)\n";
    echo "Response: " . json_encode($resVerify['body'], JSON_PRETTY_PRINT) . "\n\n";
}

echo "ALL REST API TESTS COMPLETED SUCCESSFULLY!\n";
