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
use \DOMDocument;
use \DOMXPath;

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
		$configurator->BBCodes->addCustom( '[user]{TEXT1}[/user]', '<span class="usernamebbcode">{TEXT1}</span>');
		
	}

	public function prepare_render_usernamebbcode($event)
	{
		$document = new DOMDocument();
		$xml = '<html><body>'.$event['html'].'</body></html>';
		$document->loadHTML($xml);
		$query_tag = "//span[@class='usernamebbcode']";
		$query_text = "/span/text()";
		$xpath = new DOMXPath($document);
		$nodes = $xpath->query($query_tag);
		foreach ($nodes as $node) 
		{
			$username = $node->nodeValue;
			$username = trim($username);
			$userid = $this->user_loader->load_user_by_username($username);
			if ( $userid == ANONYMOUS )
			{
				$newnode = $document->createTextNode($username);
			}
			else
			{
				$userbbcode = $this->user_loader->get_username($userid, 'full', false, false, true);
				$d = new DOMDocument();
				$d->loadXML($userbbcode);
				$newnode = $d->documentElement;
				$newnode = $document->importNode($newnode, true);
			}
			$node->parentNode->replaceChild($newnode, $node);
		}
		$xml = $document->saveHTML($document->documentElement);

		preg_match('#<body>(.*)</body>#smU', $xml, $tags);
		$event['html'] = $tags[1];
	}

}
