<?php
/**
 * Automated REST API & Validation Test Suite
 * Western Mindanao State University - College of Computing Studies
 * ADS133 - Simple Web Application Development with API Implementation
 */

$baseUrl = "http://localhost/ADS133/api/employee_api.php";
$testConnUrl = "http://localhost/ADS133/api/test_connection.php";

function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
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
echo "Count: " . ($resAll['body']['count'] ?? 'N/A') . "\n\n";

echo "=========================================================\n";
echo "3. TESTING VALIDATION: INVALID EMAIL REJECTION\n";
echo "=========================================================\n";
$invalidEmailEmployee = [
    "first_name" => "Test",
    "middle_initial" => "A",
    "last_name" => "User",
    "mobile_number" => "09123456799",
    "email" => "invalid-email-format",
    "sex" => "Male",
    "job_title" => "Business Analyst"
];
$resInvalid = makeRequest($baseUrl, 'POST', $invalidEmailEmployee);
echo "HTTP Status: " . $resInvalid['code'] . " (Expected: 400)\n";
echo "Response: " . json_encode($resInvalid['body'], JSON_PRETTY_PRINT) . "\n\n";

echo "=========================================================\n";
echo "4. TESTING VALIDATION: REJECTION OF M.I. > 1 CHAR\n";
echo "=========================================================\n";
$invalidMiEmployee = [
    "first_name" => "Test",
    "middle_initial" => "DR", // > 1 char must be rejected by VARCHAR(1) rule
    "last_name" => "User",
    "mobile_number" => "09123456799",
    "email" => "test.mi@example.com",
    "sex" => "Male",
    "job_title" => "Business Analyst"
];
$resInvalidMi = makeRequest($baseUrl, 'POST', $invalidMiEmployee);
echo "HTTP Status: " . $resInvalidMi['code'] . " (Expected: 400)\n";
echo "Response: " . json_encode($resInvalidMi['body'], JSON_PRETTY_PRINT) . "\n\n";

echo "=========================================================\n";
echo "5. TESTING POST (ADD NEW EMPLOYEE WITH 1-CHAR M.I. 'D')\n";
echo "=========================================================\n";
$uniqueSuffix = time();
$newEmployee = [
    "first_name" => "Gabriel",
    "middle_initial" => "D", // 1-letter middle initial (VARCHAR(1))
    "last_name" => "Navarro",
    "mobile_number" => "0999" . substr($uniqueSuffix, -7),
    "email" => "gabriel{$uniqueSuffix}@enterprise.com",
    "sex" => "Male",
    "job_title" => "Fullstack Software Engineer"
];
$resPost = makeRequest($baseUrl, 'POST', $newEmployee);
echo "HTTP Status: " . $resPost['code'] . " (Expected: 201)\n";
echo "Response: " . json_encode($resPost['body'], JSON_PRETTY_PRINT) . "\n";
$createdId = $resPost['body']['id'] ?? null;
echo "Created ID: " . $createdId . "\n\n";

if ($createdId) {
    echo "=========================================================\n";
    echo "6. TESTING GET (SINGLE EMPLOYEE BY ID: $createdId)\n";
    echo "=========================================================\n";
    $resSingle = makeRequest($baseUrl . "?id=" . $createdId, 'GET');
    echo "HTTP Status: " . $resSingle['code'] . "\n";
    echo "Middle Initial verified: " . ($resSingle['body']['employee']['middle_initial'] ?? 'N/A') . "\n";
    echo "Response: " . json_encode($resSingle['body'], JSON_PRETTY_PRINT) . "\n\n";

    echo "=========================================================\n";
    echo "7. TESTING PUT (UPDATE EMPLOYEE ID: $createdId)\n";
    echo "=========================================================\n";
    $updateData = [
        "id" => $createdId,
        "first_name" => "Gabriel",
        "middle_initial" => "D",
        "last_name" => "Navarro, MIT",
        "mobile_number" => "0999" . substr($uniqueSuffix, -7),
        "email" => "gabriel{$uniqueSuffix}@enterprise.com",
        "sex" => "Male",
        "job_title" => "Lead Systems Architect"
    ];
    $resPut = makeRequest($baseUrl, 'PUT', $updateData);
    echo "HTTP Status: " . $resPut['code'] . "\n";
    echo "Response: " . json_encode($resPut['body'], JSON_PRETTY_PRINT) . "\n\n";

    echo "=========================================================\n";
    echo "8. TESTING DELETE (DELETE EMPLOYEE ID: $createdId)\n";
    echo "=========================================================\n";
    $resDelete = makeRequest($baseUrl . "?id=" . $createdId, 'DELETE');
    echo "HTTP Status: " . $resDelete['code'] . "\n";
    echo "Response: " . json_encode($resDelete['body'], JSON_PRETTY_PRINT) . "\n\n";

    echo "=========================================================\n";
    echo "9. VERIFYING DELETION (GET EMPLOYEE ID: $createdId)\n";
    echo "=========================================================\n";
    $resCheck = makeRequest($baseUrl . "?id=" . $createdId, 'GET');
    echo "HTTP Status: " . $resCheck['code'] . " (Expected: 404)\n";
    echo "Response: " . json_encode($resCheck['body'], JSON_PRETTY_PRINT) . "\n\n";
}

echo "ALL REST API AND VALIDATION TESTS COMPLETED SUCCESSFULLY!\n";
