<?php
/**
 * @package     Joomla.API
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * The users controller
 *
 * @since  4.0.0
 */
class UsersController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'users';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'users';

	/**
	 * The supported filter values for date range.
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected $supportedRange
		= [
			'past_week',
			'past_1month',
			'past_3month',
			'past_6month',
			'past_6month',
			'past_year',
			'post_year',
			'today',
			'never',
		];

	/**
	 * Method to save a record.
	 *
	 * @param   integer  $recordKey  The primary key of the item (if exists)
	 *
	 * @return  integer  The record ID on success, false on failure
	 *
	 * @since   4.0.0
	 */
	protected function save($recordKey = null)
	{
		$data = (array) json_decode($this->input->json->getRaw(), true);

		foreach (FieldsHelper::getFields('com_users.user') as $field)
		{
			if (isset($data[$field->name]))
			{
				!isset($data['com_fields']) && $data['com_fields'] = [];

				$data['com_fields'][$field->name] = $data[$field->name];
				unset($data[$field->name]);
			}
		}

		$this->input->set('data', $data);

		return parent::save($recordKey);
	}

	/**
	 * User list view with filtering of data
	 *
	 * @return  static  A BaseController object to support chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function displayList()
	{
		$apiFilterInfo = $this->input->get('filter', [], 'array');
		$filter        = InputFilter::getInstance();

		if (array_key_exists('state', $apiFilterInfo))
		{
			$this->modelState->set('filter.state', $filter->clean($apiFilterInfo['state'], 'INT'));
		}

		if (array_key_exists('active', $apiFilterInfo))
		{
			$this->modelState->set('filter.active', $filter->clean($apiFilterInfo['active'], 'INT'));
		}

		if (array_key_exists('groupid', $apiFilterInfo))
		{
			$this->modelState->set('filter.group_id', $filter->clean($apiFilterInfo['groupid'], 'INT'));
		}

		if (array_key_exists('search', $apiFilterInfo))
		{
			$this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
		}

		if (array_key_exists('registrationdate', $apiFilterInfo))
		{
			$rangeFilter = $filter->clean($apiFilterInfo['registrationdate'], 'STRING');

			if (!array_key_exists($rangeFilter, $this->supportedRange))
			{
				// Send the error response
				$this->sendResponse('registrationdate', 400);
			}

			$this->modelState->set('filter.range', $rangeFilter);
		}

		if (array_key_exists('lastvisitdate', $apiFilterInfo))
		{
			$rangeFilter = $filter->clean($apiFilterInfo['lastvisitdate'], 'STRING');

			if (!array_key_exists($rangeFilter, $this->supportedRange))
			{
				// Send the error response
				$this->sendResponse('lastvisitdate', 400);
			}

			$this->modelState->set('filter.lastvisitrange', $rangeFilter);
		}

		return parent::displayList();
	}

	/**
	 * Send the given data as JSON response in the following format:
	 *
	 * {"success":true,"message":"ok","messages":null,"data":[{"type":"dir","name":"banners","path":"//"}]}
	 *
	 * @param   string   $field         The wrong field
	 * @param   integer  $responseCode  The response code
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function sendResponse(string $field = null, int $responseCode = 200): void
	{
		// Set the correct content type
		$this->app->setHeader('Content-Type', 'application/json');

		// Set the status code for the response
		http_response_code($responseCode);

		// Send the data
		echo new JsonResponse(null, Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $field), true);

		$this->app->close();
	}
}
