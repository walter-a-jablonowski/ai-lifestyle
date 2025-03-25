# Google Authentication Setup Guide

This guide will walk you through setting up Google Authentication for the AI Lifestyle web application.

## Prerequisites

- A Google Cloud Platform account
- PHP 7.4 or higher
- Composer for dependency management
- Your application must be accessible via HTTPS (for production)

## Step 1: Create a Google Cloud Project

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Click on the project dropdown at the top of the page and select "New Project"
3. Enter a project name (e.g., "AI Lifestyle") and click "Create"
4. Once the project is created, select it from the project dropdown

## Step 2: Configure OAuth Consent Screen

1. In the Google Cloud Console, navigate to "APIs & Services" > "OAuth consent screen"
2. Select "External" as the user type (unless you have a Google Workspace organization)
3. Fill in the required information:
   - App name: "AI Lifestyle"
   - User support email: Your email address
   - Developer contact information: Your email address
4. Click "Save and Continue"
5. Add the following scopes:
   - `./auth/userinfo.email`
   - `./auth/userinfo.profile`
6. Click "Save and Continue"
7. Add test users if you're in testing mode, then click "Save and Continue"
8. Review your settings and click "Back to Dashboard"

## Step 3: Create OAuth 2.0 Client ID

1. In the Google Cloud Console, navigate to "APIs & Services" > "Credentials"
2. Click "Create Credentials" and select "OAuth client ID"
3. Select "Web application" as the application type
4. Enter a name for your client (e.g., "AI Lifestyle Web Client")
5. Add authorized JavaScript origins:
   - For local development: `http://localhost` or your local development URL
   - For production: Your production domain (e.g., `https://ailifestyle.example.com`)
6. Add authorized redirect URIs:
   - For local development: `http://localhost/ai-lifestyle/src/?page=login&google_callback=1` (adjust path as needed)
   - For production: `https://ailifestyle.example.com/?page=login&google_callback=1` (adjust domain as needed)
7. Click "Create"
8. Note down the Client ID and Client Secret that are displayed

## Step 4: Update Configuration in Your Application

1. Create or update your `config.yml` file to include Google OAuth credentials:

```yml
database:
  host:     localhost
  dbname:   ai_lifestyle
  username: root
  password: ""

google_auth:
  client_id:     "YOUR_CLIENT_ID"
  client_secret: "YOUR_CLIENT_SECRET"
  redirect_uri:  "http://localhost/ai-lifestyle/src/?page=login&google_callback=1" # Adjust as needed
```

2. Update the login controller to use these credentials from the config file:

```php
// In src/pages/login/controller.php
use Symfony\Component\Yaml\Yaml;

// Load configuration
$configPath = dirname( dirname( dirname( __FILE__ ) ) ) . '/config.yml';
$config = Yaml::parseFile( $configPath );

// Google login configuration
$googleClientId = $config['google_auth']['client_id'];
$googleClientSecret = $config['google_auth']['client_secret'];
$googleRedirectUri = $config['google_auth']['redirect_uri'];
```

## Step 5: Implement the Google Login Flow

The login controller should already contain most of the code needed for Google authentication. Make sure it includes:

1. A link to the Google authorization URL
2. Code to handle the callback from Google
3. Code to exchange the authorization code for an access token
4. Code to fetch the user's profile information
5. Code to log the user in or create a new account

Here's a complete implementation for reference:

```php
// Google login configuration
$configPath = dirname( dirname( dirname( __FILE__ ) ) ) . '/config.yml';
$config = Yaml::parseFile( $configPath );

$googleClientId = $config['google_auth']['client_id'];
$googleClientSecret = $config['google_auth']['client_secret'];
$googleRedirectUri = $config['google_auth']['redirect_uri'];

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
  
  try {
    // Exchange authorization code for access token
    $tokenUrl = "https://oauth2.googleapis.com/token";
    $tokenData = [
      'client_id' => $googleClientId,
      'client_secret' => $googleClientSecret,
      'redirect_uri' => $googleRedirectUri,
      'code' => $code,
      'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init( $tokenUrl );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_POST, true );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $tokenData ) );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded'] );
    
    $tokenResponse = curl_exec( $ch );
    $tokenInfo = json_decode( $tokenResponse, true );
    
    if( isset( $tokenInfo['access_token'] ) )
    {
      // Get user info with the access token
      $userInfoUrl = "https://www.googleapis.com/oauth2/v2/userinfo";
      $ch = curl_init( $userInfoUrl );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $tokenInfo['access_token']
      ]);
      
      $userInfoResponse = curl_exec( $ch );
      $userData = json_decode( $userInfoResponse, true );
      
      if( isset( $userData['id'] ) && isset( $userData['email'] ) )
      {
        // Login or register the user
        if( $auth->googleLogin( $userData['id'], $userData['email'], $userData['name'] ?? $userData['email'] ) )
        {
          header( "Location: $baseUrl/" );
          exit;
        }
        else
          $error = "Failed to login with Google";
      }
    }
    else
      $error = "Failed to get access token from Google";
  }
  catch( Exception $e ) {
    $error = "Google login error: " . $e->getMessage();
  }
}
```

## Step 6: Test the Google Login

1. Make sure your application is running
2. Navigate to the login page
3. Click the "Login with Google" button
4. You should be redirected to Google's login page
5. After logging in with Google, you should be redirected back to your application and logged in

## Troubleshooting

### Common Issues:

1. **Redirect URI mismatch**: Make sure the redirect URI in your Google Cloud Console matches exactly with the one in your application.

2. **Invalid Client ID or Secret**: Double-check that you've copied the correct client ID and secret.

3. **CORS issues**: If you're experiencing CORS issues, make sure your domain is properly added to the authorized JavaScript origins.

4. **SSL Required**: For production, Google requires HTTPS. Make sure your production site has a valid SSL certificate.

5. **Session Issues**: If you're having trouble with sessions, make sure `session_start()` is called before any output is sent to the browser.

## Security Considerations

1. **Store credentials securely**: Never commit your client secret to version control. Use environment variables or a secure configuration file.

2. **Validate tokens**: Always validate tokens received from Google before trusting them.

3. **Use HTTPS**: Always use HTTPS in production to protect user data.

4. **Implement CSRF protection**: Consider implementing CSRF protection for your login forms.

## Additional Resources

- [Google Identity: OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server)
- [Google Sign-In for Websites](https://developers.google.com/identity/sign-in/web/sign-in)
- [Google API Client Library for PHP](https://github.com/googleapis/google-api-php-client)
