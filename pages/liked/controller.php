<?php
use AILifestyle\Config\Database;

// Set page title
$pageTitle = "Liked Content - AI Lifestyle";
$pageStyles = true;
$pageScripts = true;

// Check if user is logged in
if( ! $isLoggedIn )
{
  // Redirect to login page
  header( "Location: $baseUrl/?page=login" );
  exit;
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get current user ID
$userId = $auth->getCurrentUserId();

// Get liked widgets with pagination
$page = isset( $_GET['page_num'] ) ? (int)$_GET['page_num'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$likedWidgetsQuery = $db->prepare(
  "SELECT w.*, u.username, u.avatar_id,
   (SELECT COUNT(*) FROM likes WHERE widget_id = w.id) as like_count,
   (SELECT COUNT(*) FROM comments WHERE widget_id = w.id) as comment_count,
   1 as is_liked
   FROM widgets w
   JOIN users u ON w.user_id = u.id
   JOIN likes l ON w.id = l.widget_id
   WHERE l.user_id = ?
   ORDER BY l.created_at DESC
   LIMIT ?, ?"
);
$likedWidgetsQuery->execute( [$userId, $offset, $limit] );
$likedWidgets = $likedWidgetsQuery->fetchAll();

// Get count for pagination sum
$countQuery = $db->prepare( 
  "SELECT COUNT(*) FROM likes WHERE user_id = ?"
);
$countQuery->execute( [$userId] );
$sumLiked = $countQuery->fetchColumn();
$pagesSum = ceil( $sumLiked / $limit );

// Function to format date
function formatDate( $date )
{
  $timestamp = strtotime( $date );
  $now = time();
  $diff = $now - $timestamp;
  
  if( $diff < 60 )
    return "just now";
  elseif( $diff < 3600 )
  {
    $minutes = floor( $diff / 60 );
    return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
  }
  elseif( $diff < 86400 )
  {
    $hours = floor( $diff / 3600 );
    return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
  }
  elseif( $diff < 604800 )
  {
    $days = floor( $diff / 86400 );
    return $days . " day" . ($days > 1 ? "s" : "") . " ago";
  }
  else
    return date( "M j, Y", $timestamp );
}

// Get tags for widgets
function getWidgetTags( $widgetId, $db )
{
  $tagQuery = $db->prepare(
    "SELECT t.id, t.name
     FROM tags t
     JOIN widget_tags wt ON t.id = wt.tag_id
     WHERE wt.widget_id = ?"
  );
  $tagQuery->execute( [$widgetId] );
  return $tagQuery->fetchAll();
}
