<?php
use AILifestyle\Config\Database;

// Check if user is logged in
if( ! $isLoggedIn )
{
  echo json_encode([
    'success' => false,
    'message' => 'You must be logged in to comment'
  ]);
  exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get current user ID
$userId = $auth->getCurrentUserId();

// Get post data
$widgetId = isset( $_POST['widget_id'] ) ? (int)$_POST['widget_id'] : 0;
$content = isset( $_POST['content'] ) ? trim( $_POST['content'] ) : '';

// Validate input
if( ! $widgetId || empty( $content ) )
{
  echo json_encode([
    'success' => false,
    'message' => 'Invalid input'
  ]);
  exit;
}

// Check if widget exists
$widgetQuery = $db->prepare( "SELECT id, user_id FROM widgets WHERE id = ?" );
$widgetQuery->execute( [$widgetId] );
$widget = $widgetQuery->fetch();

if( ! $widget )
{
  echo json_encode([
    'success' => false,
    'message' => 'Widget missing'
  ]);
  exit;
}

// Insert comment
$insertQuery = $db->prepare(
  "INSERT INTO comments (widget_id, user_id, content, created_at) 
   VALUES (?, ?, ?, NOW())"
);
$insertQuery->execute( [$widgetId, $userId, $content] );
$commentId = $db->lastInsertId();

// Get comment count
$countQuery = $db->prepare( "SELECT COUNT(*) FROM comments WHERE widget_id = ?" );
$countQuery->execute( [$widgetId] );
$commentCount = $countQuery->fetchColumn();

// Get user info
$userQuery = $db->prepare( "SELECT username, avatar_id FROM users WHERE id = ?" );
$userQuery->execute( [$userId] );
$user = $userQuery->fetch();

// Format date
$createdAt = "just now";

// Create msg for widget owner
if( $widget['user_id'] != $userId )
{
  $msgQuery = $db->prepare(
    "INSERT INTO msgs (user_id, type, content, related_id, created_by, created_at, is_read) 
     VALUES (?, 'comment', ?, ?, ?, NOW(), 0)"
  );
  $msgQuery->execute( [
    $widget['user_id'],
    'commented on your post',
    $widgetId,
    $userId
  ]);
}

// Return success response
echo json_encode([
  'success' => true,
  'commentId' => $commentId,
  'commentCount' => $commentCount,
  'comment' => [
    'id' => $commentId,
    'user_id' => $userId,
    'username' => $user['username'],
    'avatar_url' => $user['avatar_id'] 
      ? "$baseUrl/uploads/{$user['avatar_id']}" 
      : "$baseUrl/assets/img/default-avatar.png",
    'content' => $content,
    'created_at' => $createdAt
  ]
]);
