<?php
/**
*
* @package phpBB Extension - Topic Subscribers
* @copyright (c) 2016 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\topicsubscribers\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string phpBB admin path */
	protected $phpbb_admin_path;

	/**
	* Constructor
	*
	* @param \phpbb\user						$user
	* @param \phpbb\template\template			$template
	* @param \phpbb\db\driver\driver_interface	$db
	* @param									$phpbb_admin_path
	*
	*/
	public function __construct(\phpbb\user $user, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, $phpbb_admin_path)
	{
		$this->user					= $user;
		$this->template				= $template;
		$this->db					= $db;
		$this->phpbb_admin_path 	= $phpbb_admin_path;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_get_post_data'		=> 'viewtopic_modify_page_title',
		);
	}

	public function viewtopic_modify_page_title($event)
	{
		$this->user->add_lang_ext('dmzx/topicsubscribers', 'common');

		$topic_id = $event['topic_id'];

		$sql= 'SELECT tw.user_id, tw.topic_id, u.user_id, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_height, u.user_avatar_width
			FROM '. TOPICS_WATCH_TABLE . ' tw
			LEFT JOIN '. USERS_TABLE . ' u
				ON tw.user_id = u.user_id
			WHERE tw.topic_id = ' . (int) $topic_id . '
			ORDER BY u.username_clean ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$avatar = phpbb_get_user_avatar($row);

			$this->template-> assign_block_vars('subscribers', array(
				'NAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'AVATAR'	=> empty($avatar) ? '<img src="' . $this->phpbb_admin_path . 'images/no_avatar.gif" width="60px;" height="60px;" alt="" />' : $avatar,
			));
		}
		$this->db->sql_freeresult($result);
	}
}
