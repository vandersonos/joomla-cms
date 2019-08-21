<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die;

$this->name = Text::_('COM_CONFIG_CACHE_SETTINGS');
$this->fieldsname = 'cache';
$this->formclass = 'options-grid-form options-grid-form-half';

echo LayoutHelper::render('joomla.content.options_default', $this);
