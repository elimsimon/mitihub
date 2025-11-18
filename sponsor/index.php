<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role_mod('sponsor');
header('Location: ' . base_url('sponsor/dashboard.php'));
