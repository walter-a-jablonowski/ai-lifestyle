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

// Get current user ID
$userId = $auth->getCurrentUserId();

// Get last check time from session or set current time as default
$lastCheckTime = $_SESSION['last_msg_check'] ?? date('Y-m-d H:i:s');

// Update last check time
$_SESSION['last_msg_check'] = date('Y-m-d H:i:s');

// Check for new msgs
$newMsgsQuery = $db->prepare(
  "SELECT COUNT(*) FROM msgs 
   WHERE user_id = ? AND created_at > ? AND is_read = 0"
);
$newMsgsQuery->execute( [$userId, $lastCheckTime] );
$newCount = $newMsgsQuery->fetchColumn();

// Get unread count
$unreadQuery = $db->prepare(
  "SELECT COUNT(*) FROM msgs 
   WHERE user_id = ? AND is_read = 0"
);
$unreadQuery->execute( [$userId] );
$unreadCount = $unreadQuery->fetchColumn();

echo json_encode([
  'success' => true,
  'hasNew' => $newCount > 0,
  'newCount' => $newCount,
  'unreadCount' => $unreadCount
]);
