<?php
use AILifestyle\Config\Database;
use AILifestyle\Utils\FileUploader;

// Set page title
$pageTitle = "Settings - AI Lifestyle";
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

// Initialize variables
$error = '';
$success = '';
$user = $auth->getCurrentUser();

// Process form submissions
if( $_SERVER['REQUEST_METHOD'] === 'POST' )
{
  $action = $_POST['action'] ?? '';
  
  if( $action === 'update_profile' )
  {
    // Update profile information
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $summary = $_POST['summary'] ?? '';
    
    if( empty( $username ) )
    {
      $error = "Username can't be empty";
    }
    elseif( empty( $email ) )
    {
      $error = "Email can't be empty";
    }
    elseif( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) )
    {
      $error = "Please enter a valid email address";
    }
    else
    {
      // Check if email is already in use
      $emailCheckQuery = $db->prepare( "SELECT id FROM users WHERE email = ? AND id != ?" );
      $emailCheckQuery->execute( [$email, $user['id']] );
      
      if( $emailCheckQuery->rowCount() > 0 )
      {
        $error = "Email is already in use";
      }
      else
      {
        $updateData = [
          'username' => $username,
          'email' => $email,
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
            if( $user['avatar_id'] )
            {
              $fileUploader->deleteFile( $user['avatar_id'] );
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
            // Refresh user data
            $user = $auth->getCurrentUser();
          }
          else
          {
            $error = "Failed to update profile";
          }
        }
      }
    }
  }
  elseif( $action === 'change_password' )
  {
    // Change password
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if( empty( $currentPassword ) || empty( $newPassword ) || empty( $confirmPassword ) )
    {
      $error = "All password fields are required";
    }
    elseif( strlen( $newPassword ) < 8 )
    {
      $error = "New password must be at least 8 characters long";
    }
    elseif( $newPassword !== $confirmPassword )
    {
      $error = "New passwords mismatch";
    }
    elseif( ! $auth->verifyPassword( $currentPassword ) )
    {
      $error = "Current password is incorrect";
    }
    else
    {
      if( $auth->changePassword( $newPassword ) )
      {
        $success = "Password changed successfully";
      }
      else
      {
        $error = "Failed to change password";
      }
    }
  }
  elseif( $action === 'msg_settings' )
  {
    // Update msg settings
    $emailMsgs = isset( $_POST['email_msgs'] ) ? 1 : 0;
    $commentMsgs = isset( $_POST['comment_msgs'] ) ? 1 : 0;
    $likeMsgs = isset( $_POST['like_msgs'] ) ? 1 : 0;
    $followMsgs = isset( $_POST['follow_msgs'] ) ? 1 : 0;
    
    $updateQuery = $db->prepare(
      "UPDATE users SET 
       email_msgs = ?,
       comment_msgs = ?,
       like_msgs = ?,
       follow_msgs = ?
       WHERE id = ?"
    );
    
    $success = $updateQuery->execute([
      $emailMsgs,
      $commentMsgs,
      $likeMsgs,
      $followMsgs,
      $user['id']
    ]);
    
    if( $success )
    {
      $success = "Msg settings updated successfully";
      // Refresh user data
      $user = $auth->getCurrentUser();
    }
    else
    {
      $error = "Failed to update msg settings";
    }
  }
  elseif( $action === 'privacy_settings' )
  {
    // Update privacy settings
    $profileVisibility = $_POST['profile_visibility'] ?? 'public';
    $showEmail = isset( $_POST['show_email'] ) ? 1 : 0;
    
    $updateQuery = $db->prepare(
      "UPDATE users SET 
       profile_visibility = ?,
       show_email = ?
       WHERE id = ?"
    );
    
    $success = $updateQuery->execute([
      $profileVisibility,
      $showEmail,
      $user['id']
    ]);
    
    if( $success )
    {
      $success = "Privacy settings updated successfully";
      // Refresh user data
      $user = $auth->getCurrentUser();
    }
    else
    {
      $error = "Failed to update privacy settings";
    }
  }
}

// Get user's msg settings
$msgQuery = $db->prepare(
  "SELECT email_msgs, comment_msgs, like_msgs, follow_msgs
   FROM users WHERE id = ?"
);
$msgQuery->execute( [$user['id']] );
$msgSettings = $msgQuery->fetch();

// Get user's privacy settings
$privacyQuery = $db->prepare(
  "SELECT profile_visibility, show_email
   FROM users WHERE id = ?"
);
$privacyQuery->execute( [$user['id']] );
$privacySettings = $privacyQuery->fetch();
