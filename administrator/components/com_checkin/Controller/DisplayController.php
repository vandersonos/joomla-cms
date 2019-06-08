<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Checkin\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Checkin Controller
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'checkin';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}

	/**
	 * Check in a list of items.
	 *
	 * @return  void
	 */
	public function checkin()
	{
		// Check for request forgeries
		$this->checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		if (empty($ids))
		{
			$this->app->enqueueMessage(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'warning');
		}
		else
		{
			// Get the model.
			/** @var \Joomla\Component\Checkin\Administrator\Model\CheckinModel $model */
			$model = $this->getModel('Checkin');

			// Checked in the items.
			$this->setMessage(Text::plural('COM_CHECKIN_N_ITEMS_CHECKED_IN', $model->checkin($ids)));
		}

		$this->setRedirect('index.php?option=com_checkin');
	}
}
