<?php
use AILifestyle\Config\Database;
use AILifestyle\Utils\FileUploader;

// Set page title
$pageTitle = "Profile - AI Lifestyle";
$pageStyles = true;
$pageScripts = true;

// Get database connection
$db = Database::getInstance()->getConnection();

// Initialize variables
$profileUser = null;
$widgets = [];
$followers = [];
$following = [];
$isOwnProfile = false;
$isFollowing = false;
$error = '';
$success = '';

// Check if viewing a specific user profile or own profile
$profileId = $_GET['id'] ?? null;

if( $profileId )
{
  // Get user data
  $userQuery = $db->prepare( "SELECT id, username, avatar_id, original_avatar_name, summary FROM users WHERE id = ?" );
  $userQuery->execute( [$profileId] );
  $profileUser = $userQuery->fetch();
  
  if( ! $profileUser )
  {
    // No user found
    $error = "User missing";
  }
  else
  {
    // Check if this is the current user's profile
    $isOwnProfile = $isLoggedIn && $profileUser['id'] == $auth->getCurrentUserId();
    
    // Check if current user is following this user
    if( $isLoggedIn && ! $isOwnProfile )
    {
      $followQuery = $db->prepare( "SELECT * FROM follows WHERE follower_id = ? AND following_id = ?" );
      $followQuery->execute( [$auth->getCurrentUserId(), $profileUser['id']] );
      $isFollowing = $followQuery->rowCount() > 0;
    }
  }
}
elseif( $isLoggedIn )
{
  // Viewing own profile
  $profileUser = $auth->getCurrentUser();
  $isOwnProfile = true;
}
else
{
  // No login in and no profile specified
  header( "Location: $baseUrl/?page=login" );
  exit;
}

// If user found, get their widgets, followers, and following
if( $profileUser )
{
  // Get user's widgets
  $widgetQuery = $db->prepare(
    "SELECT w.*, 
     (SELECT COUNT(*) FROM likes WHERE widget_id = w.id) as like_count,
     (SELECT COUNT(*) FROM comments WHERE widget_id = w.id) as comment_count,
     " . ($isLoggedIn ? "(SELECT COUNT(*) FROM likes WHERE widget_id = w.id AND user_id = ?) as is_liked" : "0 as is_liked") . "
     FROM widgets w
     WHERE w.user_id = ?
     ORDER BY w.created_at DESC
     LIMIT 10"
  );
  
  if( $isLoggedIn )
    $widgetQuery->execute( [$auth->getCurrentUserId(), $profileUser['id']] );
  else
    $widgetQuery->execute( [$profileUser['id']] );
    
  $widgets = $widgetQuery->fetchAll();
  
  // Get followers count
  $followerQuery = $db->prepare( "SELECT COUNT(*) as count FROM follows WHERE following_id = ?" );
  $followerQuery->execute( [$profileUser['id']] );
  $followerCount = $followerQuery->fetch()['count'];
  
  // Get following count
  $followingQuery = $db->prepare( "SELECT COUNT(*) as count FROM follows WHERE follower_id = ?" );
  $followingQuery->execute( [$profileUser['id']] );
  $followingCount = $followingQuery->fetch()['count'];
  
  // Get people the user follows (limited to 6 for display)
  $followingListQuery = $db->prepare(
    "SELECT u.id, u.username, u.avatar_id
     FROM users u
     JOIN follows f ON u.id = f.following_id
     WHERE f.follower_id = ?
     ORDER BY f.created_at DESC
     LIMIT 6"
  );
  $followingListQuery->execute( [$profileUser['id']] );
  $following = $followingListQuery->fetchAll();
}

// Handle widget creation/editing if this is the user's own profile
if( $isOwnProfile && $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) )
{
  $action = $_POST['action'];
  
  if( $action === 'update_profile' )
  {
    // Update profile information
    $username = $_POST['username'] ?? '';
    $summary = $_POST['summary'] ?? '';
    
    if( empty( $username ) )
    {
      $error = "Username can't be empty";
    }
    else
    {
      $updateData = [
        'username' => $username,
        'summary' => $summary
      ];
      
      // Handle avatar upload
      if( isset( $_FILES['avatar'] ) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK )
      {
        try
        {
          $fileUploader = new FileUploader();
          $uploadResult = $fileUploader->uploadImage( $_FILES['avatar'] );
          
          $updateData['avatar_id'] = $uploadResult['id'];
          $updateData['original_avatar_name'] = $uploadResult['original_name'];
          
          // Delete old avatar if exists
          if( $profileUser['avatar_id'] )
          {
            $fileUploader->deleteFile( $profileUser['avatar_id'] );
          }
        }
        catch( Exception $e )
        {
          $error = $e->getMessage();
        }
      }
      
      if( empty( $error ) )
      {
        if( $auth->updateProfile( $updateData ) )
        {
          $success = "Profile updated successfully";
          // Refresh profile data
          $profileUser = $auth->getCurrentUser();
        }
        else
        {
          $error = "Failed to update profile";
        }
      }
    }
  }
  elseif( $action === 'create_widget' )
  {
    // Create new widget
    $shortText = $_POST['short_text'] ?? '';
    $fullText = $_POST['full_text'] ?? '';
    $isHtml = isset( $_POST['is_html'] ) ? 1 : 0;
    $mediaType = $_POST['media_type'] ?? 'none';
    $mediaContent = $_POST['media_content'] ?? '';
    $tags = $_POST['tags'] ?? '';
    
    if( empty( $shortText ) )
    {
      $error = "Widget text can't be empty";
    }
    else
    {
      $widgetData = [
        'user_id' => $auth->getCurrentUserId(),
        'short_text' => $shortText,
        'full_text' => $fullText,
        'is_html' => $isHtml,
        'media_type' => $mediaType,
        'media_content' => $mediaContent
      ];
      
      // Handle image upload
      if( $mediaType === 'image' && isset( $_FILES['image'] ) && $_FILES['image']['error'] === UPLOAD_ERR_OK )
      {
        try
        {
          $fileUploader = new FileUploader();
          $uploadResult = $fileUploader->uploadImage( $_FILES['image'] );
          
          $widgetData['media_content'] = $uploadResult['filename'];
          $widgetData['original_file_name'] = $uploadResult['original_name'];
        }
        catch( Exception $e )
        {
          $error = $e->getMessage();
        }
      }
      
      if( empty( $error ) )
      {
        // Insert widget
        $insertStmt = $db->prepare(
          "INSERT INTO widgets 
           (user_id, short_text, full_text, is_html, media_type, media_content, original_file_name) 
           VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        
        $success = $insertStmt->execute([
          $widgetData['user_id'],
          $widgetData['short_text'],
          $widgetData['full_text'],
          $widgetData['is_html'],
          $widgetData['media_type'],
          $widgetData['media_content'],
          $widgetData['original_file_name'] ?? null
        ]);
        
        if( $success )
        {
          $widgetId = $db->lastInsertId();
          
          // Process tags
          if( ! empty( $tags ) )
          {
            $tagList = array_map( 'trim', explode( ',', $tags ) );
            
            foreach( $tagList as $tagName )
            {
              if( empty( $tagName ) ) continue;
              
              // Check if tag exists
              $tagQuery = $db->prepare( "SELECT id FROM tags WHERE name = ?" );
              $tagQuery->execute( [$tagName] );
              $tag = $tagQuery->fetch();
              
              if( ! $tag )
              {
                // Create new tag
                $insertTagStmt = $db->prepare( "INSERT INTO tags (name) VALUES (?)" );
                $insertTagStmt->execute( [$tagName] );
                $tagId = $db->lastInsertId();
              }
              else
              {
                $tagId = $tag['id'];
              }
              
              // Link tag to widget
              $linkTagStmt = $db->prepare( "INSERT INTO widget_tags (widget_id, tag_id) VALUES (?, ?)" );
              $linkTagStmt->execute( [$widgetId, $tagId] );
            }
          }
          
          $success = "Widget created successfully";
          
          // Refresh widgets
          $widgetQuery->execute( [$auth->getCurrentUserId(), $profileUser['id']] );
          $widgets = $widgetQuery->fetchAll();
        }
        else
        {
          $error = "Failed to create widget";
        }
      }
    }
  }
  elseif( $action === 'delete_widget' && isset( $_POST['widget_id'] ) )
  {
    $widgetId = $_POST['widget_id'];
    
    // Check if widget belongs to user
    $checkStmt = $db->prepare( "SELECT * FROM widgets WHERE id = ? AND user_id = ?" );
    $checkStmt->execute( [$widgetId, $auth->getCurrentUserId()] );
    
    if( $checkStmt->rowCount() > 0 )
    {
      $widget = $checkStmt->fetch();
      
      // Delete widget
      $deleteStmt = $db->prepare( "DELETE FROM widgets WHERE id = ?" );
      $success = $deleteStmt->execute( [$widgetId] );
      
      if( $success )
      {
        // Delete associated image if exists
        if( $widget['media_type'] === 'image' && $widget['media_content'] )
        {
          $fileUploader = new FileUploader();
          $fileUploader->deleteFile( $widget['media_content'] );
        }
        
        $success = "Widget deleted successfully";
        
        // Refresh widgets
        $widgetQuery->execute( [$auth->getCurrentUserId(), $profileUser['id']] );
        $widgets = $widgetQuery->fetchAll();
      }
      else
      {
        $error = "Failed to delete widget";
      }
    }
    else
    {
      $error = "Widget missing or you don't have permission to delete it";
    }
  }
}

// Function to format date
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
  $tagQuery->execute( [$widgetId] );
  return $tagQuery->fetchAll();
}

// Check if there are more widgets to load
$hasMoreWidgets = count( $widgets ) >= 10;
