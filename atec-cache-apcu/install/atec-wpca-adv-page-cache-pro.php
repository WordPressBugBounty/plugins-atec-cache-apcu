<?php
if (! defined('ABSPATH') ) { die('Forbidden'); }

/**
* Plugin Name: atec Cache APCu Adv
* Plugin URI: https://atecplugins.com/
* Description: APCu advanced page-cache.
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-cache-apcu-adv-page-cache
*/

define('WP_APCU_MU_PAGE_CACHE',true);
$include=WP_CONTENT_DIR.'/plugins/atec-cache-apcu/includes/atec-cache-apcu-pcache.php';
if (file_exists($include)) @include_once($include);
?>