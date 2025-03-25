<?php
use AILifestyle\Config\Database;

// Get the raw POST data
$jsonData = file_get_contents('php://input');
$errorData = json_decode($jsonData, true);

// Validate input
if (!$errorData || !isset($errorData['message'])) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid error data'
  ]);
  exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Prepare error data
$errorType = isset($errorData['type']) ? $errorData['type'] : 'client';
$errorMessage = $errorData['message'];
$errorDetails = isset($errorData['details']) ? json_encode($errorData['details']) : null;
$userId = $isLoggedIn ? $auth->getCurrentUserId() : null;
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
$requestUrl = $_SERVER['HTTP_REFERER'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

// Log error to database
$insertQuery = $db->prepare(
  "INSERT INTO error_logs (
    error_type, error_message, error_details, user_id, 
    user_agent, request_url, ip_address, created_at
  ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
);

$insertQuery->execute([
  $errorType,
  $errorMessage,
  $errorDetails,
  $userId,
  $userAgent,
  $requestUrl,
  $ipAddress
]);

// Return success response
echo json_encode([
  'success' => true
]);
