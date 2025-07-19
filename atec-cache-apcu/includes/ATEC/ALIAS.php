<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\TOOLS;

final class ALIAS
{

// NEW: introduced 250710 | CLEANUP: change ALIAS -> TOOLS
public static function dash_yes_no($yes) 
{ return '<span class="'.TOOLS::dash_class($yes?'yes-alt' : 'dismiss', 'atec-'.($yes?'green' : 'red')).'"></span>'; }

// NEW: introduced 250710 | CLEANUP: change ALIAS -> TOOLS
public static function tr(...$args)
{ return TOOLS::table_tr(...$args); }

// NEW: introduced 250710 | CLEANUP: change ALIAS -> TOOLS
public static function td(...$args)
{ return TOOLS::table_td(...$args); }

}
