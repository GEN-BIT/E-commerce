<?php
/**
 * Include this at the top of every admin/*.php page.
 * Stage: 3 - Authentication
 */

require_once __DIR__ . '/../config/config.php';

if (!is_admin()) {
    redirect('index.php');
}
