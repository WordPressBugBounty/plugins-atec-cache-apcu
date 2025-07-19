<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;
use ATEC\WPCA;

final class Comments
{
	public static function init()
	{
		// Hook into new comment insertion
		add_action('comment_post', [__CLASS__, 'on_comment_post']);
		// Hook into comment status transitions
		add_action('transition_comment_status', [__CLASS__, 'on_comment_transition'], 10, 3);
	}

	public static function on_comment_post($comment_ID)
	{
		$comment = get_comment($comment_ID);
		if ($comment && $comment->comment_approved == 1) self::delete_comment($comment);
	}

	public static function on_comment_transition($new_status, $old_status, $comment)
	{
		if (in_array($new_status, ['trash', 'approved'], true)) self::delete_comment($comment);
	}

	public static function delete_comment($comment)
	{
		$salt = WPCA::settings('salt');
		if (!class_exists('ATEC_WPCA\\Tools')) require __DIR__ . '/atec-wpca-pcache-tools.php';
		\ATEC_WPCA\Tools::delete_page($salt, 'p', $comment->comment_post_ID);
	}
}

// Initialize comment-based cache invalidation
Comments::init();
?>