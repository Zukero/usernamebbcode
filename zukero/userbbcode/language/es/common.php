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
		1	=> '<strong>Mencionado</strong> por %1$s en:',
	),
	'NOTIFICATION_TYPE_USERNAME'		=> 'Alguien le menciona en un mensaje con la etiqueta [user]',
	'BBCODE_USERNAME_HELP'				=> 'Mencione a un usuario con la etiqueta [user]username[/user]',
	'BBCODE_USERNAME_BUTTON'			=> 'Usuario',
));
