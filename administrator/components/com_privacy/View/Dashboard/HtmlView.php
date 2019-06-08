<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\View\Dashboard;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Privacy\Administrator\Helper\PrivacyHelper;
use Joomla\Component\Privacy\Administrator\Model\DashboardModel;
use Joomla\Component\Privacy\Administrator\Model\RequestsModel;

/**
 * Dashboard view class
 *
 * @since  3.9.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Number of urgent requests based on the component configuration
	 *
	 * @var    integer
	 * @since  3.9.0
	 */
	protected $numberOfUrgentRequests;

	/**
	 * Information about whether a privacy policy is published
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $privacyPolicyInfo;

	/**
	 * The request counts
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $requestCounts;

	/**
	 * Information about whether a menu item for the request form is published
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $requestFormPublished;

	/**
	 * Flag indicating the site supports sending email
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $sendMailEnabled;

	/**
	 * Days when a request is considered urgent
	 *
	 * @var    integer
	 * @since  3.9.0
	 */
	protected $urgentRequestDays = 14;

	/**
	 * Id of the system privacy consent plugin
	 *
	 * @var    integer
	 * @since  3.9.2
	 */
	protected $privacyConsentPluginId;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @see     BaseHtmlView::loadTemplate()
	 * @since   3.9.0
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		/** @var DashboardModel $model */
		$model                        = $this->getModel();
		$this->privacyPolicyInfo      = $model->getPrivacyPolicyInfo();
		$this->requestCounts          = $model->getRequestCounts();
		$this->requestFormPublished   = $model->getRequestFormPublished();
		$this->privacyConsentPluginId = PrivacyHelper::getPrivacyConsentPluginId();
		$this->sendMailEnabled        = (bool) Factory::getConfig()->get('mailonline', 1);

		/** @var RequestsModel $requestsModel */
		$requestsModel = $this->getModel('requests');

		$this->numberOfUrgentRequests = $requestsModel->getNumberUrgentRequests();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->urgentRequestDays = (int) ComponentHelper::getParams('com_privacy')->get('notify', 14);

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_PRIVACY_VIEW_DASHBOARD'), 'lock');

		ToolbarHelper::preferences('com_privacy');

		ToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_DASHBOARD');
	}
}
