<?php
/**
 * Common bootstrap file for Mitihub
 * Defines ROOT_PATH and includes essential configuration
 */

// Define root path for consistent includes across environments
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__));
}

// Helper function to get relative path to root
function get_root_path() {
    return dirname(__FILE__);
}

// Load core configuration
require_once ROOT_PATH . '/app/config.php';

// Quick maintenance-mode: if a .maintenance file exists at project root, return 503 quickly.
$maintenanceFile = ROOT_PATH . '/.maintenance';
if (php_sapi_name() !== 'cli' && file_exists($maintenanceFile)) {
    http_response_code(503);
    header('Retry-After: 3600');
    echo "<html><head><title>Maintenance</title></head><body style='font-family:Arial,Helvetica,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#f8fafc;'><div style='max-width:640px;padding:24px;text-align:center;border-radius:12px;background:#fff;box-shadow:0 8px 30px rgba(0,0,0,.06);'><h1 style='margin:0 0 .5rem'>We'll be back soon</h1><p style='color:#555;margin:0 0 1rem;'>The site is temporarily down for maintenance. Please try again later.</p></div></body></html>";
    exit;
}

// Attempt to enable gzip output if possible; otherwise start normal buffering.
if (php_sapi_name() !== 'cli') {
    if (!headers_sent() && function_exists('ob_gzhandler')) {
        @ob_start('ob_gzhandler');
    } else {
        @ob_start();
    }
}