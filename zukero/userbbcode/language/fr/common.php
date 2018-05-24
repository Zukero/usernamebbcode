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
		1	=> '<strong>Mentionné</strong> par %1$s dans :',
	),
	'NOTIFICATION_TYPE_USERNAME'		=> 'Quelqu’un vous a mentionné dans un message avec la balise [user]',
	'BBCODE_USERNAME_HELP'				=> 'Mentionner un utilisateur avec la balise [user]nom_utilisateur[/user]',
	'BBCODE_USERNAME_BUTTON'			=> 'Utilisateur',
));
