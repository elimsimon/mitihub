<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role_mod('school');
header('Location: ' . base_url('school/dashboard.php'));
