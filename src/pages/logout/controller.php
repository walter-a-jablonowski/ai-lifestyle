<?php
use AILifestyle\Utils\Auth;

// Logout the user
$auth->logout();

// Redirect to home page
header( "Location: $baseUrl/" );
exit;
