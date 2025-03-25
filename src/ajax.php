<?php
require_once __DIR__ . '/vendor/autoload.php';

use AILifestyle\Utils\ErrorHandler;
use AILifestyle\Utils\Auth;

// Register error handlers
ErrorHandler::registerHandlers();

// Initialize authentication
$auth = Auth::getInstance();

// Get request data
$requestData = json_decode( file_get_contents( 'php://input' ), true );
if( ! $requestData )
{
  // Fallback to $_POST for form data
  $requestData = $_POST;
}

// Get the requested action
$action = $_GET['action'] ?? '';

// Validate action name to prevent directory traversal
if( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $action ) )
{
  sendJsonError( "Invalid action" );
}

// Validate handler name to prevent directory traversal
$handler = $_GET['handler'] ?? 'index';
if( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $handler ) )
{
  sendJsonError( "Invalid handler" );
}

// Check if action handler exists
$actionFile = __DIR__ . "/pages/{$action}/ajax/{$handler}.php";
if( ! file_exists( $actionFile ) )
{
  sendJsonError('No action found');
}

// Include the action handler
try
{
  require_once $actionFile;
}
catch( Exception $e )
{
  sendJsonError( $e->getMessage() );
}

/**
 * Send a JSON success response
 */
function sendJsonSuccess( $data = null, $message = "Success" )
{
  header( 'Content-Type: application/json' );
  echo json_encode( [
    'success' => true,
    'message' => $message,
    'data' => $data
  ] );
  exit;
}

/**
 * Send a JSON error response
 */
function sendJsonError( $message = "Error", $code = 400 )
{
  http_response_code( $code );
  header( 'Content-Type: application/json' );
  echo json_encode( [
    'success' => false,
    'message' => $message
  ] );
  exit;
}
