<?php
/**
 *
 * Username BBCode. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Zukero, github.com/Zukero
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'NOTIFICATION_USERNAME'	=> array(
		1	=> '<strong>Mentioned</strong> by %1$s in:',
	),
	'NOTIFICATION_TYPE_USERNAME'		=> 'Someone mentions you in a post with a [user] tag',
	'BBCODE_USERNAME_HELP'				=> 'Mention a user with [user]username[/user] tags',
	'BBCODE_USERNAME_BUTTON'			=> 'User',
));
