<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once ROOT_PATH . '/app/auth.php';
require_role_mod('admin');
header('Location: ' . base_url('admin/dashboard.php'));
