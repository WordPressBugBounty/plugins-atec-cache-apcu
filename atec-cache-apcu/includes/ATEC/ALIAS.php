<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\TOOLS;

final class ALIAS
{

public static function tr(...$args)
{
	return TOOLS::table_tr(...$args);
}

public static function td(...$args)
{
	return TOOLS::table_td(...$args);
}

}
