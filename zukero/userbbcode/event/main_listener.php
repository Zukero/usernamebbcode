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
		
		//Add the @username and @"user name" syntax
		$configurator->Preg->match( '/@(?<content>[^\\s]+)/', 'USER');
		$configurator->Preg->match( '/@"(?<content>.+?)"/', 'USER');
	}

	public function prepare_render_usernamebbcode($event)
	{
		$document = new DOMDocument();
		$xml = '<html><head><meta charset="utf-8" /></head><body>'.$event['html'].'</body></html>';
		$document->loadHTML($xml);
		$query_tag = "//span[@class='usernamebbcode']";
		$xpath = new DOMXPath($document);
		$nodes = $xpath->query($query_tag);
		foreach ($nodes as $node) 
		{
			$username = $node->nodeValue;
			$username = htmlspecialchars($username);
			$username = trim($username);
			$userid = $this->user_loader->load_user_by_username($username);
			if ( $userid == ANONYMOUS )
			{
				$newnode = $document->createTextNode(htmlspecialchars_decode($username));
				$node->parentNode->replaceChild($newnode, $node);
			}
			else
			{
				$userbbcode = $this->user_loader->get_username($userid, 'full', false, false, true);
				$d = new DOMDocument();
				$d->loadXML('<?xml version="1.0" encoding="UTF-8"?><username>'.$userbbcode.'</username>');
				$tags = $d->documentElement->childNodes;
				$tags = iterator_to_array($tags);
				$tags = array_reverse($tags);
				$first = true;
				foreach ($tags as $tag)
				{
					$tag = $document->importNode($tag, true);
					if ($first) 
					{
						$node->parentNode->replaceChild($tag, $node);
						$node = $tag;
						$first = false;
					}
					else
					{
						$node = $node->parentNode->insertBefore($tag, $node);
					}
				}
			}
		}
		$body = $document->getElementsByTagName('body')->item(0);
		$nodes = $body->childNodes;
		$html = '';
		foreach ($nodes as $node)
		{
			$html .= $document->saveHTML($node);
		}
		$html = $document->saveHTML($body);
		$content = substr($html, strlen('<body>'), -strlen('</body>'));
		$event['html'] = $html;
	}

}
