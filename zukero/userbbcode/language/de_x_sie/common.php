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
		1	=> '<strong>Erwähnt</strong> von %1$s in:',
	),
	'NOTIFICATION_TYPE_USERNAME'		=> 'Jemand hat Sie im Beitrag mit dem [user] BBCode erwähnt.',
	'BBCODE_USERNAME_HELP'				=> 'Erwähnen Sie einen User mit dem [user]Username[/user] BBCode.',
	'BBCODE_USERNAME_BUTTON'			=> 'User',
));
