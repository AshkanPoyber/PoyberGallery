<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

session_start();
logoutUser();
session_start();
setFlash('info', 'You have been logged out.');
redirect('login.php');
