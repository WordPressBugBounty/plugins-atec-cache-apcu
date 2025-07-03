<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {	exit; }
if (!defined('ATEC_LOADER')) require __DIR__ . '/includes/ATEC/LOADER.php';

delete_option('atec_wpca_last_cache');

\ATEC\INIT::delete_settings('wpca');
?>