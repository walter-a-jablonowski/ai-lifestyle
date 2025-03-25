<?php
use AILifestyle\Utils\Auth;

// Set page title
$pageTitle = "Register - AI Lifestyle";
$pageStyles = true;
$pageScripts = true;

// Check if user is already logged in
if( $auth->isLoggedIn() )
{
  // Redirect to home page
  header( "Location: $baseUrl/" );
  exit;
}

// Process registration form submission
$error = '';
$success = '';

if( $_SERVER['REQUEST_METHOD'] === 'POST' )
{
  $username = $_POST['username'] ?? '';
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';
  
  // Validate input
  if( empty( $username ) || empty( $email ) || empty( $password ) || empty( $confirmPassword ) )
  {
    $error = "All fields are required";
  }
  elseif( strlen( $username ) < 3 || strlen( $username ) > 50 )
  {
    $error = "Username must be between 3 and 50 characters";
  }
  elseif( !filter_var( $email, FILTER_VALIDATE_EMAIL ) )
  {
    $error = "Please enter a valid email address";
  }
  elseif( strlen( $password ) < 8 )
  {
    $error = "Password must be at least 8 characters long";
  }
  elseif( $password !== $confirmPassword )
  {
    $error = "Passwords mismatch";
  }
  else
  {
    try
    {
      if( $auth->register( $username, $email, $password ) )
      {
        // Redirect to home page
        header( "Location: $baseUrl/" );
        exit;
      }
      else
      {
        $error = "Registration failed. Please try again.";
      }
    }
    catch( Exception $e )
    {
      $error = $e->getMessage();
    }
  }
}
