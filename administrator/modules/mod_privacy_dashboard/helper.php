<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseModel;

/**
 * Helper class for admin privacy dashboard module
 *
 * @since  __DEPLOY_VERSION__
 */
class ModPrivacyDashboardHelper
{
	/**
	 * Method to retrieve information about the site privacy requests
	 *
	 * @return  array  Array containing site privacy requests
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getData()
	{
		BaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_privacy/models', 'PrivacyModel');

		/** @var PrivacyModelDashboard $model */
		$model = BaseModel::getInstance('Dashboard', 'PrivacyModel');

		try
		{
			return $model->getRequestCounts();
		}
		catch (JDatabaseException $e)
		{
			return array();
		}
	}
}
