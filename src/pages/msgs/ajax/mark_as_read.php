<?php
use AILifestyle\Config\Database;

// Check if user is logged in
if( ! $isLoggedIn )
{
  echo json_encode([
    'success' => false,
    'message' => 'User un-authenticated'
  ]);
  exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get msg ID
$msgId = $_GET['id'] ?? null;

if( ! $msgId )
{
  echo json_encode([
    'success' => false,
    'message' => 'Msg ID is required'
  ]);
  exit;
}

// Get current user ID
$userId = $auth->getCurrentUserId();

// Mark msg as read
$markQuery = $db->prepare( "UPDATE msgs SET is_read = 1 WHERE id = ? AND user_id = ?" );
$result = $markQuery->execute( [$msgId, $userId] );

echo json_encode([
  'success' => $result,
  'message' => $result ? 'Msg marked as read' : 'Failed to mark msg as read'
]);
