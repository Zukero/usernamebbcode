<?php
/**
 *
 * Username BBCode. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Zukero, github.com/Zukero
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace zukero\userbbcode\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User BBCode Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.text_formatter_s9e_configure_after' => 'configure_usernamebbcode',
			'core.text_formatter_s9e_render_after'     => 'prepare_render_usernamebbcode',
		);
	}
	
	/** @var \phpbb\user_loader */
	protected $user_loader;
	
	/** @var \phpbb\template\template */
	protected $template;
	
	/** @var \phpbb\language\language */
	protected $language;

	
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\template\template $template, \phpbb\language\language $language)
	{
		$this->user_loader = $user_loader;
		$this->template = $template;
		$this->language = $language;
	}

	public function configure_usernamebbcode($event)
	{
		$this->language->add_lang('common', 'zukero/userbbcode');
		// Get the BBCode configurator 
		$configurator = $event['configurator']; 

		// Let's unset any existing BBCode that might already exist 
		unset($configurator->BBCodes['user']); 
		unset($configurator->tags['user']); 

		// Let's create the new BBCode 
		$configurator->BBCodes->addCustom( '[user]{TEXT1}[/user]', '[user]{TEXT1}[/user]');
		
	}

	public function prepare_render_usernamebbcode($event)
	{
		preg_match_all('#\[user\]\s*([a-zA-Z0-9._\-\/\s]+?)\s*\[\/user\]#is', $event['html'], $tags); 
		for ($i = 0; $i < sizeof($tags[0]); $i++)
		{
			$username = $tags[1][$i];
			$userid = $this->user_loader->load_user_by_username($username);
			$user = $this->user_loader->get_user($userid,  true);
			
			if ( $user == false or $userid == ANONYMOUS )
			{
				$userbbcode = $username;
			}
			else
			{
				$userbbcode = $this->user_loader->get_username($userid, 'full', false, false, true);
			}
			$event['html'] = str_replace($tags[0][$i], $userbbcode, $event['html']);
		}
	}

}
