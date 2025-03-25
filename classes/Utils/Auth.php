<?php
namespace AILifestyle\Utils;

use AILifestyle\Config\Database;

class Auth
{
  private static $instance = null;
  private $db;
  private $user = null;

  private function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
    $this->checkSession();
  }

  public static function getInstance() : self
  {
    if( self::$instance === null )
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function checkSession()
  {
    session_start();
    
    if( isset( $_SESSION['user_id'] ) )
    {
      $stmt = $this->db->prepare( "SELECT id, username, email, avatar_id, original_avatar_name, summary FROM users WHERE id = ?" );
      $stmt->execute( [$_SESSION['user_id']] );
      $this->user = $stmt->fetch();
    }
  }

  public function register( $username, $email, $password ) : bool
  {
    // Check if username or email already exists
    $stmt = $this->db->prepare( "SELECT id FROM users WHERE username = ? OR email = ?" );
    $stmt->execute( [$username, $email] );
    
    if( $stmt->rowCount() > 0 )
    {
      throw new \Exception( "Username or email already exists" );
    }

    // Hash password
    $hashedPassword = password_hash( $password, PASSWORD_DEFAULT );
    
    // Insert new user
    $stmt = $this->db->prepare( "INSERT INTO users (username, email, password) VALUES (?, ?, ?)" );
    $success = $stmt->execute( [$username, $email, $hashedPassword] );
    
    if( $success )
    {
      // Log the user in
      $userId = $this->db->lastInsertId();
      $this->login( $email, $password );
      return true;
    }
    
    return false;
  }

  public function login( $email, $password ) : bool
  {
    $stmt = $this->db->prepare( "SELECT id, username, email, password, avatar_id, original_avatar_name, summary FROM users WHERE email = ?" );
    $stmt->execute( [$email] );
    $user = $stmt->fetch();
    
    if( $user && password_verify( $password, $user['password'] ) )
    {
      // Store user data in session
      $_SESSION['user_id'] = $user['id'];
      $this->user = $user;
      return true;
    }
    
    return false;
  }

  public function googleLogin( $googleId, $email, $username )
  {
    // Check if user with this Google ID exists
    $stmt = $this->db->prepare( "SELECT id, username, email, avatar_id, original_avatar_name, summary FROM users WHERE google_id = ?" );
    $stmt->execute( [$googleId] );
    $user = $stmt->fetch();
    
    if( $user )
    {
      // User exists, log them in
      $_SESSION['user_id'] = $user['id'];
      $this->user = $user;
      return true;
    }
    else
    {
      // Check if email exists
      $stmt = $this->db->prepare( "SELECT id FROM users WHERE email = ?" );
      $stmt->execute( [$email] );
      
      if( $stmt->rowCount() > 0 )
      {
        // Link Google ID to existing account
        $user = $stmt->fetch();
        $updateStmt = $this->db->prepare( "UPDATE users SET google_id = ? WHERE id = ?" );
        $updateStmt->execute( [$googleId, $user['id']] );
        
        // Log user in
        $_SESSION['user_id'] = $user['id'];
        $this->checkSession(); // Refresh user data
        return true;
      }
      else
      {
        // Create new user
        $randomPassword = bin2hex( random_bytes( 16 ) );
        $hashedPassword = password_hash( $randomPassword, PASSWORD_DEFAULT );
        
        $stmt = $this->db->prepare( "INSERT INTO users (username, email, password, google_id) VALUES (?, ?, ?, ?)" );
        $success = $stmt->execute( [$username, $email, $hashedPassword, $googleId] );
        
        if( $success )
        {
          $userId = $this->db->lastInsertId();
          $_SESSION['user_id'] = $userId;
          $this->checkSession(); // Refresh user data
          return true;
        }
      }
    }
    
    return false;
  }

  public function logout()
  {
    session_unset();
    session_destroy();
    $this->user = null;
  }

  public function isLoggedIn() : bool
  {
    return $this->user !== null;
  }

  public function getCurrentUser()
  {
    return $this->user;
  }

  public function getCurrentUserId()
  {
    return $this->user ? $this->user['id'] : null;
  }

  public function updateProfile( $data )
  {
    if( ! $this->isLoggedIn() )
    {
      throw new \Exception('No user logged in');
    }

    $allowedFields = ['username', 'summary', 'avatar_id', 'original_avatar_name'];
    $updates = [];
    $params = [];

    foreach( $data as $field => $value )
    {
      if( in_array( $field, $allowedFields ) )
      {
        $updates[] = "$field = ?";
        $params[] = $value;
      }
    }

    if( empty( $updates ) )
    {
      return false;
    }

    // Add user ID to params
    $params[] = $this->getCurrentUserId();

    $stmt = $this->db->prepare( "UPDATE users SET " . implode( ", ", $updates ) . " WHERE id = ?" );
    $success = $stmt->execute( $params );

    if( $success )
    {
      // Refresh user data
      $this->checkSession();
    }

    return $success;
  }

  /**
   * Verify if the provided password matches the current user's password
   * 
   * @param string $password The password to verify
   * @return bool True if password is correct, or false
   */
  public function verifyPassword( $password ) : bool
  {
    if( ! $this->isLoggedIn() )
    {
      return false;
    }
    
    // Get current user's password hash
    $stmt = $this->db->prepare( "SELECT password FROM users WHERE id = ?" );
    $stmt->execute( [$this->getCurrentUserId()] );
    $userData = $stmt->fetch();
    
    if( ! $userData )
    {
      return false;
    }
    
    // Verify password
    return password_verify( $password, $userData['password'] );
  }
  
  /**
   * Change the current user's password
   * 
   * @param string $newPassword The new password
   * @return bool True if password was changed successfully, or false
   */
  public function changePassword( $newPassword ) : bool
  {
    if( ! $this->isLoggedIn() )
    {
      return false;
    }
    
    // Hash the new password
    $hashedPassword = password_hash( $newPassword, PASSWORD_DEFAULT );
    
    // Update password in database
    $stmt = $this->db->prepare( "UPDATE users SET password = ? WHERE id = ?" );
    $success = $stmt->execute( [$hashedPassword, $this->getCurrentUserId()] );
    
    return $success;
  }
}
