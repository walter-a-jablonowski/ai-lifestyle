<?php
use AILifestyle\Config\Database;

// Set page title
$pageTitle = "Search - AI Lifestyle";
$pageStyles = true;
$pageScripts = true;

// Get database connection
$db = Database::getInstance()->getConnection();

// Initialize variables
$query = $_GET['q'] ?? '';
$tag = $_GET['tag'] ?? '';
$results = [];
$relatedTags = [];
$hasSearch = !empty($query) || !empty($tag);

// Get search results
if ($hasSearch) {
  // Prepare base query
  $baseQuery = "
    SELECT w.*, u.username, u.avatar_id,
    (SELECT COUNT(*) FROM likes WHERE widget_id = w.id) as like_count,
    (SELECT COUNT(*) FROM comments WHERE widget_id = w.id) as comment_count,
    " . ($isLoggedIn ? "(SELECT COUNT(*) FROM likes WHERE widget_id = w.id AND user_id = ?) as is_liked" : "0 as is_liked") . "
    FROM widgets w
    JOIN users u ON w.user_id = u.id
  ";
  
  $params = [];
  if ($isLoggedIn) {
    $params[] = $auth->getCurrentUserId();
  }
  
  // Search by tag
  if (!empty($tag)) {
    $baseQuery .= "
      JOIN widget_tags wt ON w.id = wt.widget_id
      JOIN tags t ON wt.tag_id = t.id
      WHERE t.name = ?
    ";
    $params[] = $tag;
  }
  // Search by query
  elseif (!empty($query)) {
    $baseQuery .= "
      WHERE w.short_text LIKE ? 
      OR w.full_text LIKE ?
      OR u.username LIKE ?
    ";
    $searchTerm = "%{$query}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
  }
  
  // Order by and limit
  $baseQuery .= "
    ORDER BY w.created_at DESC
    LIMIT 20
  ";
  
  // Execute query
  $stmt = $db->prepare($baseQuery);
  $stmt->execute($params);
  $results = $stmt->fetchAll();
  
  // Get related tags
  if (!empty($results)) {
    $widgetIds = array_column($results, 'id');
    $placeholders = str_repeat('?,', count($widgetIds) - 1) . '?';
    
    $tagQuery = $db->prepare("
      SELECT DISTINCT t.name, COUNT(wt.widget_id) as count
      FROM tags t
      JOIN widget_tags wt ON t.id = wt.tag_id
      WHERE wt.widget_id IN ({$placeholders})
      " . (!empty($tag) ? "AND t.name != ?" : "") . "
      GROUP BY t.name
      ORDER BY count DESC
      LIMIT 10
    ");
    
    $tagParams = $widgetIds;
    if (!empty($tag)) {
      $tagParams[] = $tag;
    }
    
    $tagQuery->execute($tagParams);
    $relatedTags = $tagQuery->fetchAll();
  }
}

// Get trending tags
$trendingQuery = $db->prepare("
  SELECT t.name, COUNT(wt.widget_id) as count
  FROM tags t
  JOIN widget_tags wt ON t.id = wt.tag_id
  JOIN widgets w ON wt.widget_id = w.id
  WHERE w.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
  GROUP BY t.name
  ORDER BY count DESC
  LIMIT 10
");
$trendingQuery->execute();
$trendingTags = $trendingQuery->fetchAll();

// Function to format date
function formatDate($date) {
  $timestamp = strtotime($date);
  $now = time();
  $diff = $now - $timestamp;
  
  if ($diff < 60) {
    return "just now";
  } elseif ($diff < 3600) {
    $minutes = floor($diff / 60);
    return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
  } elseif ($diff < 86400) {
    $hours = floor($diff / 3600);
    return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
  } elseif ($diff < 604800) {
    $days = floor($diff / 86400);
    return $days . " day" . ($days > 1 ? "s" : "") . " ago";
  } else {
    return date("M j, Y", $timestamp);
  }
}

// Get tags for widgets
function getWidgetTags($widgetId, $db) {
  $tagQuery = $db->prepare(
    "SELECT t.id, t.name
     FROM tags t
     JOIN widget_tags wt ON t.id = wt.tag_id
     WHERE wt.widget_id = ?"
  );
  $tagQuery->execute([$widgetId]);
  return $tagQuery->fetchAll();
}
