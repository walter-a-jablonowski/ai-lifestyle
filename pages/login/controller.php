<?php
use AILifestyle\Utils\Auth;

// Set page title
$pageTitle = "Login - AI Lifestyle";
$pageStyles = true;
$pageScripts = true;

// Check if user is already logged in
if( $auth->isLoggedIn() )
{
  // Redirect to home page
  header( "Location: $baseUrl/" );
  exit;
}

// Process login form submission
$error = '';
$success = '';

if( $_SERVER['REQUEST_METHOD'] === 'POST' )
{
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  
  if( empty( $email ) || empty( $password ) )
  {
    $error = "Please enter email and password";
  }
  else
  {
    try
    {
      if( $auth->login( $email, $password ) )
      {
        // Redirect to home page
        header( "Location: $baseUrl/" );
        exit;
      }
      else
      {
        $error = "Invalid email or password";
      }
    }
    catch( Exception $e )
    {
      $error = $e->getMessage();
    }
  }
}

// Google login configuration
$googleClientId = "YOUR_GOOGLE_CLIENT_ID"; // Replace with actual client ID
$googleRedirectUri = "$baseUrl/?page=login&google_callback=1";
$googleAuthUrl = "https://accounts.google.com/o/oauth2/v2/auth";
$googleAuthUrl .= "?client_id=" . urlencode( $googleClientId );
$googleAuthUrl .= "&redirect_uri=" . urlencode( $googleRedirectUri );
$googleAuthUrl .= "&response_type=code";
$googleAuthUrl .= "&scope=" . urlencode( "https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile" );
$googleAuthUrl .= "&prompt=select_account";

// Handle Google login callback
if( isset( $_GET['google_callback'] ) && isset( $_GET['code'] ) )
{
  $code = $_GET['code'];
  
  // This would be implemented with actual Google API credentials
  // For now, we'll just show a message that this needs to be configured
  $error = "Google login unconfigured. Please update the Google client ID and secret in the login controller.";
  
  // The actual implementation would look like this:
  /*
  try {
    // Exchange code for access token
    $tokenUrl = "https://oauth2.googleapis.com/token";
    $postData = [
      'code' => $code,
      'client_id' => $googleClientId,
      'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
      'redirect_uri' => $googleRedirectUri,
      'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($response, true);
    
    if (isset($tokenData['access_token'])) {
      // Get user info with access token
      $userInfoUrl = "https://www.googleapis.com/oauth2/v2/userinfo";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $tokenData['access_token']
      ]);
      $userInfo = curl_exec($ch);
      curl_close($ch);
      
      $userData = json_decode($userInfo, true);
      
      // Login or register user
      if ($auth->googleLogin($userData['id'], $userData['email'], $userData['name'])) {
        header("Location: $baseUrl/");
        exit;
      } else {
        $error = "Failed to login with Google";
      }
    } else {
      $error = "Failed to get access token from Google";
    }
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
  */
}
