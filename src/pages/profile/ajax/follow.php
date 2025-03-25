<?php
use AILifestyle\Config\Database;

// Check if user is logged in
if( ! $isLoggedIn )
{
  echo json_encode([
    'success' => false,
    'message' => 'You must be logged in to follow users'
  ]);
  exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get user ID to follow/unfollow
$userId = $_POST['user_id'] ?? null;

if( ! $userId )
{
  echo json_encode([
    'success' => false,
    'message' => 'Invalid user ID'
  ]);
  exit;
}

// Check if user exists
$userQuery = $db->prepare( "SELECT id FROM users WHERE id = ?" );
$userQuery->execute( [$userId] );

if( $userQuery->rowCount() === 0 )
{
  echo json_encode([
    'success' => false,
    'message' => 'User missing'
  ]);
  exit;
}

// Check if already following
$followQuery = $db->prepare( "SELECT * FROM follows WHERE follower_id = ? AND following_id = ?" );
$followQuery->execute( [$auth->getCurrentUserId(), $userId] );
$isFollowing = $followQuery->rowCount() > 0;

if( $isFollowing )
{
  // Unfollow
  $unfollowQuery = $db->prepare( "DELETE FROM follows WHERE follower_id = ? AND following_id = ?" );
  $success = $unfollowQuery->execute( [$auth->getCurrentUserId(), $userId] );
  
  if( $success )
  {
    echo json_encode([
      'success' => true,
      'action' => 'unfollow',
      'message' => 'User unfollowed successfully'
    ]);
  }
  else
  {
    echo json_encode([
      'success' => false,
      'message' => 'Failed to unfollow user'
    ]);
  }
}
else
{
  // Follow
  $followQuery = $db->prepare( "INSERT INTO follows (follower_id, following_id) VALUES (?, ?)" );
  $success = $followQuery->execute( [$auth->getCurrentUserId(), $userId] );
  
  if( $success )
  {
    echo json_encode([
      'success' => true,
      'action' => 'follow',
      'message' => 'User followed successfully'
    ]);
  }
  else
  {
    echo json_encode([
      'success' => false,
      'message' => 'Failed to follow user'
    ]);
  }
}
