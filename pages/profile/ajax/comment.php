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

// Get widget ID and comment content
$widgetId = $_POST['widget_id'] ?? null;
$content = $_POST['content'] ?? '';

if( ! $widgetId || empty( $content ) )
{
  echo json_encode([
    'success' => false,
    'message' => 'Invalid widget ID or empty comment'
  ]);
  exit;
}

// Check if widget exists
$widgetQuery = $db->prepare( "SELECT id FROM widgets WHERE id = ?" );
$widgetQuery->execute( [$widgetId] );

if( $widgetQuery->rowCount() === 0 )
{
  echo json_encode([
    'success' => false,
    'message' => 'Widget missing'
  ]);
  exit;
}

// Add comment
$commentQuery = $db->prepare( "INSERT INTO comments (user_id, widget_id, content) VALUES (?, ?, ?)" );
$success = $commentQuery->execute( [$auth->getCurrentUserId(), $widgetId, $content] );

if( $success )
{
  // Get the new comment ID
  $commentId = $db->lastInsertId();
  
  // Get comment with user info
  $commentInfoQuery = $db->prepare(
    "SELECT c.*, u.username, u.avatar_id
     FROM comments c
     JOIN users u ON c.user_id = u.id
     WHERE c.id = ?"
  );
  $commentInfoQuery->execute( [$commentId] );
  $comment = $commentInfoQuery->fetch();
  
  // Format date
  $timestamp = strtotime( $comment['created_at'] );
  $now = time();
  $diff = $now - $timestamp;
  
  if( $diff < 60 )
  {
    $formattedDate = "just now";
  }
  elseif( $diff < 3600 )
  {
    $minutes = floor( $diff / 60 );
    $formattedDate = $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
  }
  elseif( $diff < 86400 )
  {
    $hours = floor( $diff / 3600 );
    $formattedDate = $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
  }
  elseif( $diff < 604800 )
  {
    $days = floor( $diff / 86400 );
    $formattedDate = $days . " day" . ($days > 1 ? "s" : "") . " ago";
  }
  else
  {
    $formattedDate = date( "M j, Y", $timestamp );
  }
  
  // Get updated comment count
  $countQuery = $db->prepare( "SELECT COUNT(*) as count FROM comments WHERE widget_id = ?" );
  $countQuery->execute( [$widgetId] );
  $commentCount = $countQuery->fetch()['count'];
  
  // Get avatar URL
  $avatarUrl = $comment['avatar_id'] 
    ? "$baseUrl/uploads/{$comment['avatar_id']}" 
    : "$baseUrl/assets/img/default-avatar.png";
  
  echo json_encode([
    'success' => true,
    'commentCount' => $commentCount,
    'comment' => [
      'id' => $comment['id'],
      'content' => $comment['content'],
      'created_at' => $formattedDate,
      'user_id' => $comment['user_id'],
      'username' => $comment['username'],
      'avatar_url' => $avatarUrl
    ],
    'message' => 'Comment added successfully'
  ]);
}
else
{
  echo json_encode([
    'success' => false,
    'message' => 'Failed to add comment'
  ]);
}
