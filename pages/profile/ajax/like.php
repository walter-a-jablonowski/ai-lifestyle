<?php
use AILifestyle\Config\Database;

// Check if user is logged in
if( ! $isLoggedIn )
{
  echo json_encode([
    'success' => false,
    'message' => 'You must be logged in to like widgets'
  ]);
  exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get widget ID
$widgetId = $_POST['widget_id'] ?? null;

if( ! $widgetId )
{
  echo json_encode([
    'success' => false,
    'message' => 'Invalid widget ID'
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

// Check if already liked
$likeQuery = $db->prepare( "SELECT * FROM likes WHERE user_id = ? AND widget_id = ?" );
$likeQuery->execute( [$auth->getCurrentUserId(), $widgetId] );
$isLiked = $likeQuery->rowCount() > 0;

if( $isLiked )
{
  // Unlike
  $unlikeQuery = $db->prepare( "DELETE FROM likes WHERE user_id = ? AND widget_id = ?" );
  $success = $unlikeQuery->execute( [$auth->getCurrentUserId(), $widgetId] );
  
  if( $success )
  {
    // Get updated like count
    $countQuery = $db->prepare( "SELECT COUNT(*) as count FROM likes WHERE widget_id = ?" );
    $countQuery->execute( [$widgetId] );
    $likeCount = $countQuery->fetch()['count'];
    
    echo json_encode([
      'success' => true,
      'action' => 'unlike',
      'likeCount' => $likeCount,
      'message' => 'Widget unliked successfully'
    ]);
  }
  else
  {
    echo json_encode([
      'success' => false,
      'message' => 'Failed to unlike widget'
    ]);
  }
}
else
{
  // Like
  $likeQuery = $db->prepare( "INSERT INTO likes (user_id, widget_id) VALUES (?, ?)" );
  $success = $likeQuery->execute( [$auth->getCurrentUserId(), $widgetId] );
  
  if( $success )
  {
    // Get updated like count
    $countQuery = $db->prepare( "SELECT COUNT(*) as count FROM likes WHERE widget_id = ?" );
    $countQuery->execute( [$widgetId] );
    $likeCount = $countQuery->fetch()['count'];
    
    echo json_encode([
      'success' => true,
      'action' => 'like',
      'likeCount' => $likeCount,
      'message' => 'Widget liked successfully'
    ]);
  }
  else
  {
    echo json_encode([
      'success' => false,
      'message' => 'Failed to like widget'
    ]);
  }
}
