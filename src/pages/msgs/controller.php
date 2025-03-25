<?php
use AILifestyle\Config\Database;

// Set page title
$pageTitle = "Msgs - AI Lifestyle";
$pageStyles = true;
$pageScripts = true;

// Check if user is logged in
if( ! $isLoggedIn )
{
  // Redirect to login page
  header("Location: $baseUrl/?page=login");
  exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get current user ID
$userId = $auth->getCurrentUserId();

// Mark msgs as read if requested
if( isset( $_GET['mark_as_read'] ) )
{
  $msgId = $_GET['mark_as_read'];
  
  if( $msgId === 'all' )
  {
    // Mark all msgs as read
    $markAllQuery = $db->prepare( "UPDATE msgs SET is_read = 1 WHERE user_id = ?" );
    $markAllQuery->execute( [$userId] );
  }
  else
  {
    // Mark specific msg as read
    $markQuery = $db->prepare( "UPDATE msgs SET is_read = 1 WHERE id = ? AND user_id = ?" );
    $markQuery->execute( [$msgId, $userId] );
  }
  
  // Redirect to remove query string
  header( "Location: $baseUrl/?page=msgs" );
  exit;
}

// Delete msg if requested
if( isset( $_GET['delete'] ) )
{
  $msgId = $_GET['delete'];
  
  if( $msgId === 'all' )
  {
    // Delete all msgs
    $deleteAllQuery = $db->prepare( "DELETE FROM msgs WHERE user_id = ?" );
    $deleteAllQuery->execute( [$userId] );
  }
  else
  {
    // Delete specific msg
    $deleteQuery = $db->prepare( "DELETE FROM msgs WHERE id = ? AND user_id = ?" );
    $deleteQuery->execute( [$msgId, $userId] );
  }
  
  // Redirect to remove query string
  header( "Location: $baseUrl/?page=msgs" );
  exit;
}

// Get msgs
$page = isset( $_GET['page_num'] ) ? (int)$_GET['page_num'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$msgsQuery = $db->prepare(
  "SELECT n.*, u.username, u.avatar_id 
   FROM msgs n
   LEFT JOIN users u ON n.actor_id = u.id
   WHERE n.user_id = ?
   ORDER BY n.created_at DESC
   LIMIT ?, ?"
);
$msgsQuery->execute( [$userId, $offset, $limit] );
$msgs = $msgsQuery->fetchAll();

// Get count for pagination
$countQuery = $db->prepare( "SELECT COUNT(*) FROM msgs WHERE user_id = ?" );
$countQuery->execute( [$userId] );
$sumMsgs = $countQuery->fetchColumn();
$pagesSum = ceil( $sumMsgs / $limit );

// Get unread count
$unreadQuery = $db->prepare( "SELECT COUNT(*) FROM msgs WHERE user_id = ? AND is_read = 0" );
$unreadQuery->execute( [$userId] );
$unreadCount = $unreadQuery->fetchColumn();
