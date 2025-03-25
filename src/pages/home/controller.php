<?php
use AILifestyle\Config\Database;

// Set page title
$pageTitle = "AI Lifestyle - Share your AI-age lifestyle";
$pageStyles = true;
$pageScripts = true;

// Get database connection
$db = Database::getInstance()->getConnection();

// Initialize variables
$widgets = [];
$trendingTags = [];

// Get trending tags
$tagQuery = $db->query(
  "SELECT t.id, t.name, COUNT(wt.widget_id) as count 
   FROM tags t 
   JOIN widget_tags wt ON t.id = wt.tag_id 
   GROUP BY t.id 
   ORDER BY count DESC 
   LIMIT 10"
);
$trendingTags = $tagQuery->fetchAll();

// If user is logged in, get content from people they follow
if( $isLoggedIn )
{
  $userId = $auth->getCurrentUserId();
  
  // Get widgets from people the user follows
  $followingQuery = $db->prepare(
    "SELECT w.*, u.username, u.avatar_id, 
     (SELECT COUNT(*) FROM likes WHERE widget_id = w.id) as like_count,
     (SELECT COUNT(*) FROM comments WHERE widget_id = w.id) as comment_count,
     (SELECT COUNT(*) FROM likes WHERE widget_id = w.id AND user_id = ?) as is_liked
     FROM widgets w
     JOIN users u ON w.user_id = u.id
     JOIN follows f ON w.user_id = f.following_id
     WHERE f.follower_id = ?
     ORDER BY w.created_at DESC
     LIMIT 10"
  );
  $followingQuery->execute([$userId, $userId]);
  $followingWidgets = $followingQuery->fetchAll();
  
  // If no following anyone or no content, get trending content
  if( empty( $followingWidgets ) )
  {
    $trendingQuery = $db->prepare(
      "SELECT w.*, u.username, u.avatar_id, 
       (SELECT COUNT(*) FROM likes WHERE widget_id = w.id) as like_count,
       (SELECT COUNT(*) FROM comments WHERE widget_id = w.id) as comment_count,
       (SELECT COUNT(*) FROM likes WHERE widget_id = w.id AND user_id = ?) as is_liked
       FROM widgets w
       JOIN users u ON w.user_id = u.id
       LEFT JOIN likes l ON w.id = l.widget_id
       GROUP BY w.id
       ORDER BY COUNT(l.widget_id) DESC, w.created_at DESC
       LIMIT 10"
    );
    $trendingQuery->execute([$userId]);
    $widgets = $trendingQuery->fetchAll();
  }
  else
  {
    $widgets = $followingWidgets;
  }
}
else
{
  // Get trending content for non-logged in users
  $trendingQuery = $db->query(
    "SELECT w.*, u.username, u.avatar_id, 
     (SELECT COUNT(*) FROM likes WHERE widget_id = w.id) as like_count,
     (SELECT COUNT(*) FROM comments WHERE widget_id = w.id) as comment_count,
     0 as is_liked
     FROM widgets w
     JOIN users u ON w.user_id = u.id
     LEFT JOIN likes l ON w.id = l.widget_id
     GROUP BY w.id
     ORDER BY COUNT(l.widget_id) DESC, w.created_at DESC
     LIMIT 10"
  );
  $widgets = $trendingQuery->fetchAll();
}

// Function to format widget data for display
function formatDate( $date )
{
  $timestamp = strtotime( $date );
  $now = time();
  $diff = $now - $timestamp;
  
  if( $diff < 60 )
  {
    return "just now";
  }
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
  {
    return date( "M j, Y", $timestamp );
  }
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
  $tagQuery->execute([$widgetId]);
  return $tagQuery->fetchAll();
}

// Check if there are more widgets to load
$hasMoreWidgets = count( $widgets ) >= 10;
