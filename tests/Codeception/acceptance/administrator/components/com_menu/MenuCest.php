<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Page\Acceptance\Administrator\MenuListPage;
use Page\Acceptance\Administrator\MenuFormPage;
use Page\Acceptance\Administrator\AdminPage;

/**
 * Administrator Menu Tests
 *
 * @since  4.0.0
 */
class MenuCest
{
	/**
	 * Create a menu
	 *
	 * @param   AcceptanceTester  $I  The AcceptanceTester Object
	 *
	 * @since  4.0.0
	 *
	 * @return  void
	 */
	public function createNewMenu(\AcceptanceTester $I)
	{
		$I->comment('I am going to create a menu');
		$I->doAdministratorLogin();

		$I->amOnPage(MenuListPage::$url);
		$I->checkForPhpNoticesOrWarnings();

		$I->waitForText(MenuListPage::$pageTitleText);
		$I->click('#menu-collapse-icon');

		$I->clickToolbarButton('new');
		$I->waitForText(MenuFormPage::$pageTitleText);
		$I->checkForPhpNoticesOrWarnings();

		$this->fillMenuInformation($I, 'Test Menu');

		$I->clickToolbarButton('save');
		$I->waitForText(MenuListPage::$pageTitleText);
		$I->checkForPhpNoticesOrWarnings();
	}


	/**
	 * Fill out the menu information form
	 *
	 * @param   AcceptanceTester  $I            The AcceptanceTester Object
	 * @param   string            $title        Title
	 * @param   string            $type         Type of the menu
	 * @param   string            $description  Description
	 *
	 * @since  4.0.0
	 *
	 * @return  void
	 */
	protected function fillMenuInformation($I, $title, $type = 'Test', $description = 'Automated Testing')
	{
		$I->fillField(MenuFormPage::$fieldTitle, $title);
		$I->fillField(MenuFormPage::$fieldMenuType, $type);
		$I->fillField(MenuFormPage::$fieldMenuDescription, $description);
	}
}
