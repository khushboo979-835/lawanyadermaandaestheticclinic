<?php
/**
 * API Endpoint: Submit Consultation Inquiry
 * Records patient consultation leads from website forms into the admin database
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/config/db.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Receive data from POST or JSON payload
$data = $_POST;
if (empty($data)) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $json = json_decode($rawInput, true);
        if (is_array($json)) {
            $data = $json;
        }
    }
}

$name = trim($data['name'] ?? '');
$phone = trim($data['phone'] ?? '');
$email = trim($data['email'] ?? '');
$age = trim($data['age'] ?? '');
$service = trim($data['service'] ?? '');
$message = trim($data['message'] ?? '');
$source = trim($data['source'] ?? 'website');

if (empty($name) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name and phone are required']);
    exit;
}

try {
    $pdo = get_db_connection();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO inquiries (name, phone, email, age, service, message, source, ip_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new')");
    $stmt->execute([$name, $phone, $email, $age, $service, $message, $source, $ip]);

    echo json_encode([
        'success' => true,
        'message' => 'Consultation request recorded successfully',
        'inquiry_id' => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
