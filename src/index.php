<?php
require_once __DIR__ . '/vendor/autoload.php';

use AILifestyle\Utils\ErrorHandler;
use AILifestyle\Utils\Auth;

// Register error handlers
ErrorHandler::registerHandlers();

// Initialize authentication
$auth = Auth::getInstance();

// Determine which page to load
$page = $_GET['page'] ?? 'home';

// Validate page name to prevent directory traversal
if( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $page ) )
  $page = 'home';

// Check if page exists
$pagePath = __DIR__ . "/pages/$page";
if( ! is_dir( $pagePath ) )
{
  $page = 'home';
  $pagePath = __DIR__ . "/pages/$page";
}

// Check if controller exists
$controllerFile = "$pagePath/controller.php";
if( ! file_exists( $controllerFile ) )
{
  ErrorHandler::displayErrorPage('Page missing', 'The requested page missing.');
  exit;
}

// Define common variables for views
$baseUrl = rtrim( dirname( $_SERVER['PHP_SELF'] ), '/' );
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();

// Include the controller
require_once $controllerFile;

// Start output buffering
ob_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'AI Lifestyle' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= $baseUrl ?>/shared/style.css">
  <?php if( isset( $pageStyles ) ): ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/pages/<?= $page ?>/style.css">
  <?php endif; ?>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="<?= $baseUrl ?>/">AI Lifestyle</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarMain">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link <?= $page === 'home' ? 'active' : '' ?>" href="<?= $baseUrl ?>/">Home</a>
          </li>
          <?php if( $isLoggedIn ): ?>
            <li class="nav-item">
              <a class="nav-link <?= $page === 'profile' ? 'active' : '' ?>" href="<?= $baseUrl ?>/?page=profile">My Profile</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $page === 'liked' ? 'active' : '' ?>" href="<?= $baseUrl ?>/?page=liked">Liked Content</a>
            </li>
          <?php endif; ?>
        </ul>
        
        <form class="d-flex mx-auto" action="<?= $baseUrl ?>/?page=search" method="GET">
          <input type="hidden" name="page" value="search">
          <input class="form-control me-2" type="search" name="q" placeholder="Search content..." aria-label="Search">
          <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
        </form>
        
        <ul class="navbar-nav ms-auto">
          <?php if( $isLoggedIn ): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                <?= htmlspecialchars( $currentUser['username'] ) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/?page=profile">My Profile</a></li>
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/?page=settings">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= $baseUrl ?>/?page=logout">Logout</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link <?= $page === 'login' ? 'active' : '' ?>" href="<?= $baseUrl ?>/?page=login">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $page === 'register' ? 'active' : '' ?>" href="<?= $baseUrl ?>/?page=register">Register</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="container py-4">
    <?php 
      if( file_exists("$pagePath/view.php"))
        include "$pagePath/view.php";
      else
        echo "<div class='alert alert-danger'>View file missing for page: $page</div>";
    ?>
  </main>

  <!-- Footer -->
  <footer class="bg-light py-4 mt-auto">
    <div class="container text-center">
      <p class="mb-0">&copy; <?= date('Y') ?> AI Lifestyle</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= $baseUrl ?>/controller.js"></script>
  <script src="<?= $baseUrl ?>/error-handler.js"></script>
  <?php if( isset( $pageScripts ) ): ?>
    <script src="<?= $baseUrl ?>/pages/<?= $page ?>/controller.js"></script>
  <?php endif; ?>
</body>
</html>
<?php

// Flush the output buffer
ob_end_flush();

?>
