<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Privacy.uscontenter
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('PrivacyPlugin', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/plugin.php');

/**
 * Privacy plugin managing Joomla user content data
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPrivacyContent extends PrivacyPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Affects constructor behaviour. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Contents array
	 *
	 * @var    Array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $contents = array();

	/**
	 * Processes an export request for Joomla core user content data
	 *
	 * This event will collect data for the content core table
	 *
	 * - Content custom fields
	 *
	 * @param   PrivacyTableRequest  $request  The request record being processed
	 *
	 * @return  PrivacyExportDomain[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPrivacyExportRequest(PrivacyTableRequest $request)
	{
		if (!$request->user_id)
		{
			return array();
		}

		/** @var JTableUser $user */
		$user = JUser::getTable();
		$user->load($request->user_id);

		$domains   = array();
		$domains[] = $this->createContentDomain($user);

		foreach ($this->contents as $content)
		{
			$domains[] = $this->createContentCustomFieldsDomain($content);
		}
	
		return $domains;
	}

	/**
	 * Create the domain for the user content data
	 *
	 * @param   JTableUser  $user  The JTableUser object to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function createContentDomain(JTableUser $user)
	{
		$domain = $this->createDomain('user content', 'Joomla! user content data');

		$query = $this->db->getQuery(true)
			->select('*')
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('created_by') . ' = ' . $this->db->quote($user->id))
			->order($this->db->quoteName('ordering') . ' ASC');

		$items = $this->db->setQuery($query)->loadAssocList();

		foreach ($items as $item)
		{
			$domain->addItem($this->createItemFromArray($item));
			$this->contents[] = (object) $item;
		}

		return $domain;
	}

	/**
	 * Create the domain for the content custom fields
	 *
	 * @param   Object  $content  The content to process
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function createContentCustomFieldsDomain($content)
	{
		$domain = $this->createDomain('content custom fields', 'Joomla! content custom fields data');

		// Get item's fields, also preparing their value property for manual display
		$fields = FieldsHelper::getFields('com_content.article', $content);

		foreach ($fields as $field)
		{
			$fieldValue = is_array($field->value) ? implode(', ', $field->value) : $field->value;

			$data = array(
				'content_id'  => $content->id,
				'field_name'  => $field->name,
				'field_title' => $field->title,
				'field_value' => $fieldValue,
			);

			$domain->addItem($this->createItemFromArray($data));
		}

		return $domain;
	}
}
