<?php
/**
 * PELITA - Admin Logout
 */

require_once __DIR__ . '/../config/app.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';

logout();
redirect('admin/login.php');
