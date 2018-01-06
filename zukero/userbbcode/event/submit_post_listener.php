<?php
/**
 *
 * User BBCode. An extension for the phpBB Forum Software package.
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
class submit_post_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup' => 'load_lang',
			'core.submit_post_end' => 'notify_mentioned_users',
			'core.markread_before' => 'mark_mentioned_users_notifications_read'
		);
	}
	
	/** @var \phpbb\notification\manager */
	protected $phpbb_notifications;
	
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(\phpbb\notification\manager $phpbb_notifications, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\user $user)
	{
		$this->phpbb_notifications = $phpbb_notifications;
		$this->db = $db;
		$this->language = $language;
		$this->user = $user;
	}

	public function load_lang($event)
	{
		$this->language->add_lang('common', 'zukero/userbbcode');
	} 
	
	public function notify_mentioned_users($event)
	{
	
		/*
		* @var	string	mode				Variable containing posting mode value
		* @var	string	subject				Variable containing post subject value
		* @var	string	username			Variable containing post author name
		* @var	int		topic_type			Variable containing topic type value
		* @var	array	poll				Array with the poll data for the post
		* @var	array	data				Array with the data for the post
		* @var	int		post_visibility		Variable containing up to date post visibility
		* @var	bool	update_message		Flag indicating if the post will be updated
		* @var	bool	update_search_index	Flagcore.markread_before indicating if the search index will be updated
		* @var	string	url	
		*/
		
		$data = $event['data'];
		$username = $event['username'];
		$subject = $event['subject'];
		$mode = $event['mode'];
		$post_visibility = $event['post_visibility'];
		if (!empty($data_ary['post_time']))
		{
			$current_time = $data_ary['post_time'];
		}
		else
		{
			$current_time = time();
		}
		// Send Notifications
		$notification_data = array_merge($data, array(
			'topic_title'		=> (isset($data['topic_title'])) ? $data['topic_title'] : $subject,
			'post_username'		=> $username,
			'poster_id'			=> $data['poster_id'],
			'post_text'			=> $data['message'],
			'post_time'			=> $current_time, 
			'post_subject'		=> $subject,
		));

		if ($post_visibility == ITEM_APPROVED)
		{
			switch ($mode)
			{
				case 'post':
					$this->phpbb_notifications->add_notifications(
						'zukero.userbbcode.notification.type.username',
						$notification_data);
				break;

				case 'reply':
				case 'quote':
					$this->phpbb_notifications->add_notifications(
						'zukero.userbbcode.notification.type.username',
						$notification_data);
				break;

				case 'edit_topic':
				case 'edit_first_post':
				case 'edit':
				case 'edit_last_post':
					$this->phpbb_notifications->update_notifications(
						'zukero.userbbcode.notification.type.username',
						$notification_data);
				break;
			}
		}
		else if ($post_visibility == ITEM_UNAPPROVED)
		{
			switch ($mode)
			{
				case 'post':
				case 'reply':
				case 'quote':
				case 'edit_topic':
				case 'edit_first_post':
				case 'edit':
				case 'edit_last_post':
					// Nothing to do here
				break;
			}
		}
		else if ($post_visibility == ITEM_REAPPROVE)
		{
			switch ($mode)
			{
				case 'edit_topic':
				case 'edit_first_post':
				case 'edit':
				case 'edit_last_post':
				case 'post':
				case 'reply':
				case 'quote':
					// Nothing to do here
				break;
			}
		}
		else if ($post_visibility == ITEM_DELETED)
		{
			switch ($mode)
			{
				case 'post':
				case 'reply':
				case 'quote':
				case 'edit_topic':
				case 'edit_first_post':
				case 'edit':
				case 'edit_last_post':
					// Nothing to do here
				break;
			}
		}
	}
	
	
	public function mark_mentioned_users_notifications_read($event)
	{
	/**
	 * This event is used for performing actions directly before marking forums,
	 * topics or posts as read.
	 *
	 * It is also possible to prevent the marking. For that, the $should_markread parameter
	 * should be set to FALSE.
	 *
	 * @event core.markread_before
	 * @var	string	mode				Variable containing marking mode value
	 * @var	mixed	forum_id			Variable containing forum id, or false
	 * @var	mixed	topic_id			Variable containing topic id, or false
	 * @var	int		post_time			Variable containing post time
	 * @var	int		user_id				Variable containing the user id
	 * @var	bool	should_markread		Flag indicating if the markread should be done or not.
	 * @since 3.1.4-RC1
	 */
		extract($event->get_data());
		$user_id = $this->user->data['user_id'];
		if ($mode == 'all')
		{
			if (empty($forum_id))
			{
			
				// Mark all topic notifications read for this user
				$this->phpbb_notifications->mark_notifications(array(
					'zukero.userbbcode.notification.type.username'), 
					false, $user_id, $post_time);

			}

			return;
		}
		else if ($mode == 'topics')
		{
			// Mark all topics in forums read
			if (!is_array($forum_id))
			{
				$forum_id = array($forum_id);
			}
			else
			{
				$forum_id = array_unique($forum_id);
			}
			
			// Mark all post/quote notifications read for this user in this forum
			$topic_ids = array();
			$sql = 'SELECT topic_id
				FROM ' . TOPICS_TABLE . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_id);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$topic_ids[] = $row['topic_id'];
			}
			$this->db->sql_freeresult($result);
			
			$this->phpbb_notifications->mark_notifications_by_parent(array(
				'zukero.userbbcode.notification.type.username'), 
				$topic_ids, $user_id, $post_time);

			return;
		}
		else if ($mode == 'topic')
		{
			if ($topic_id === false || $forum_id === false)
			{
				return;
			}
		
			// Mark post notifications read for this user in this topic
			$this->phpbb_notifications->mark_notifications_by_parent(array(
				'zukero.userbbcode.notification.type.username'),
				$topic_id, $user_id, $post_time);

			return;
		}
	} 

}
