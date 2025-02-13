<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_fs { 
	
private $afs;

public function fix_separator($str): string { return (DIRECTORY_SEPARATOR==='/')?$str:str_replace('/',DIRECTORY_SEPARATOR,$str); }
private function prefix($p): string { return $p==='mega-cache'?'':'atec-'; }
public function upload_dir($p): string { return $this->fix_separator(wp_get_upload_dir()['basedir'].'/'.$this->prefix($p).$p); }

//public function get_contents($path) { return $this->afs->exists($path)?$this->afs->get_contents($path):false; }		// string|false
//public function put_contents($path,$content,$mode = false): bool { return $this->afs->put_contents($path,$content,$mode); }
public function copy($source,$target,$overwrite=true,$mode = false): bool { return $this->afs->copy($source,$target,$overwrite,$mode); }
//public function delete($path, $recursive = false, $type = false): bool { return $this->afs->exists($path)?$this->afs->delete($path,$recursive,$type):true; }
public function dirlist($path, $include_hidden = true, $recursive = false) { return $this->afs->dirlist($path, $include_hidden, $recursive); }		// array|false
public function getchmod($path): string { return $this->afs->exists($path)?$this->afs->getchmod($path):false; }		// string|false
public function mkdir($dir, $chmod = false, $chown = false, $chgrp = false): bool 
{ if ($this->afs->exists($dir)) return true; return $this->afs->mkdir($dir,$chmod,$chown,$chgrp); } // default FS_CHMOD_DIR = 0755
public function move($source,$target,$overwrite=true): bool { return $this->afs->move($source,$target,$overwrite); }
public function mtime($path) { return $this->afs->exists($path)?$this->afs->mtime($path):false; } 	//  int|false
public function rmdir($dir,$recursive = false): bool { return $this->afs->rmdir($dir,$recursive); }	
public function size($path) { return $this->afs->size($path); } 	//  int|false
public function touch($path, $time=0, $atime=0): bool { return $this->afs->touch($path,$time,$atime); }

// @codingStandardsIgnoreStart
public function exists($path): bool { return @file_exists($path); }		// file or directory
public function install($dir,$uploadDir,$arr,&$s) : void
{ foreach($arr as $key=>$value) { $s = $s && @copy(plugin_dir_path($dir).'install'.DIRECTORY_SEPARATOR.$key, $uploadDir.DIRECTORY_SEPARATOR.$value); } }
public function is_dir($dir): bool { @is_dir($dir); }	
public function get($path,$default=false) { return @file_exists($path)?@file_get_contents($path):$default; }		// string|false
public function put($path,$content,$flags = 0) { return @file_put_contents($path,$content,$flags); } 	//  int|false, can use $flags = FILE_APPEND
public function unlink($path): bool { return @file_exists($path)?@unlink($path):true; }
// @codingStandardsIgnoreEnd

function __construct() {

if (!function_exists('get_file_description')) @require(ABSPATH.'/wp-admin/includes/file.php'); 
global $wp_filesystem; WP_Filesystem();
$this->afs = $wp_filesystem;

}}
?>