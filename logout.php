<?php
require_once __DIR__ . '/config/session.php';

// Destroy session
Session::destroy();

// Redirect to login
header('Location: /e-TU/index.php');
exit;
