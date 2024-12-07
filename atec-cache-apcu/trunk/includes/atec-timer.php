<?php
if (!defined( 'ABSPATH' )) { exit; }
define('ATEC_TIMER_INC',true);
wp_cache_set('atec_start_timer',microtime(true)); 
 
function atec_stop_timer(): void
{ 
  echo '<span class="atec-fs-12" title="', esc_attr__('Execution time','atec_footer'), '"><span class="atec-fs-12" class="',esc_attr(atec_dash_class('clock')), '"></span> ', esc_attr(intval((microtime(true) - wp_cache_get('atec_start_timer'))*1000)), ' <span class="atec-fs-10">ms</span></span>'; 
}
?>