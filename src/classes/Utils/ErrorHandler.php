<?php
namespace AILifestyle\Utils;

class ErrorHandler
{
  public static function handleError( $errno, $errstr, $errfile, $errline )
  {
    self::displayErrorPage( "PHP Error", "$errstr in $errfile on line $errline" );
    return true; // Prevent PHP's default error handler
  }

  public static function handleException( $exception )
  {
    self::displayErrorPage( "Application Error", $exception->getMessage() );
  }

  public static function displayErrorPage( $title, $message )
  {
    // Clear any output that might have been sent
    if( ob_get_length() )
    {
      ob_clean();
    }

    // Set appropriate headers
    header( "HTTP/1.1 500 Internal Server Error" );
    header( "Content-Type: text/html; charset=UTF-8" );

    // Display user-friendly error page
    echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error - AI Lifestyle</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background-color: #f8f9fa;
    }
    .error-container {
      max-width: 500px;
      padding: 2rem;
      background-color: white;
      border-radius: 0.5rem;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      text-align: center;
    }
    .error-icon {
      font-size: 4rem;
      color: #dc3545;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <div class="error-icon">⚠️</div>
    <h1 class="h4 mb-3">' . htmlspecialchars( $title ) . '</h1>
    <p class="text-muted">' . htmlspecialchars( $message ) . '</p>
    <a href="/" class="btn btn-primary mt-3">Return to Home</a>
  </div>
</body>
</html>';
    exit;
  }

  public static function registerHandlers()
  {
    set_error_handler( [self::class, 'handleError'] );
    set_exception_handler( [self::class, 'handleException'] );
  }
}
