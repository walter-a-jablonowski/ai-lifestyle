<?php
use AILifestyle\Config\Database;

// Set page title
$pageTitle = "Widget - AI Lifestyle";
$pageStyles = true;
$pageScripts = true;

// Get database connection
$db = Database::getInstance()->getConnection();

// Get widget ID from URL
$widgetId = $_GET['id'] ?? 0;

if( ! $widgetId )
{
  // Redirect to home if no widget ID provided
  header( "Location: $baseUrl/?page=home" );
  exit;
}

// Get widget details
$widgetQuery = $db->prepare(
  "SELECT w.*, u.username, u.avatar_id, u.summary,
   (SELECT COUNT(*) FROM likes WHERE widget_id = w.id) as like_count,
   (SELECT COUNT(*) FROM comments WHERE widget_id = w.id) as comment_count,
   " . ($isLoggedIn ? "(SELECT COUNT(*) FROM likes WHERE widget_id = w.id AND user_id = ?) as is_liked" : "0 as is_liked") . ",
   " . ($isLoggedIn ? "(SELECT COUNT(*) FROM follows WHERE followed_id = w.user_id AND follower_id = ?) as is_following" : "0 as is_following") . "
   FROM widgets w
   JOIN users u ON w.user_id = u.id
   WHERE w.id = ?"
);

$params = [];
if( $isLoggedIn )
{
  $params[] = $auth->getCurrentUserId();
  $params[] = $auth->getCurrentUserId();
}
$params[] = $widgetId;

$widgetQuery->execute( $params );
$widget = $widgetQuery->fetch();

if( ! $widget )
{
  // Widget missing, redirect to home
  header( "Location: $baseUrl/?page=home" );
  exit;
}

// Check privacy settings
if( $widget['profile_visibility'] === 'private' && $widget['user_id'] !== ($isLoggedIn ? $auth->getCurrentUserId() : 0) )
{
  // Widget belongs to a private profile, redirect to home
  header( "Location: $baseUrl/?page=home" );
  exit;
}

if( $widget['profile_visibility'] === 'followers' && 
    (!$isLoggedIn || ($widget['user_id'] !== $auth->getCurrentUserId() && !$widget['is_following'])) )
{
  // Widget belongs to a followers-only profile and user is no following, redirect to home
  header( "Location: $baseUrl/?page=home" );
  exit;
}

// Update page title
$pageTitle = htmlspecialchars( substr( $widget['short_text'], 0, 50 ) ) . " - AI Lifestyle";

// Get tags for this widget
$tagsQuery = $db->prepare(
  "SELECT t.id, t.name
   FROM tags t
   JOIN widget_tags wt ON t.id = wt.tag_id
   WHERE wt.widget_id = ?"
);
$tagsQuery->execute( [$widgetId] );
$tags = $tagsQuery->fetchAll();

// Get comments for this widget
$commentsQuery = $db->prepare(
  "SELECT c.*, u.username, u.avatar_id
   FROM comments c
   JOIN users u ON c.user_id = u.id
   WHERE c.widget_id = ?
   ORDER BY c.created_at ASC"
);
$commentsQuery->execute( [$widgetId] );
$comments = $commentsQuery->fetchAll();

// Get related widgets based on tags
$relatedWidgetsQuery = $db->prepare(
  "SELECT DISTINCT w.id, w.short_text, w.created_at, u.username, u.avatar_id
   FROM widgets w
   JOIN widget_tags wt1 ON w.id = wt1.widget_id
   JOIN widget_tags wt2 ON wt1.tag_id = wt2.tag_id
   JOIN users u ON w.user_id = u.id
   WHERE wt2.widget_id = ? AND w.id != ?
   ORDER BY w.created_at DESC
   LIMIT 5"
);
$relatedWidgetsQuery->execute( [$widgetId, $widgetId] );
$relatedWidgets = $relatedWidgetsQuery->fetchAll();

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
