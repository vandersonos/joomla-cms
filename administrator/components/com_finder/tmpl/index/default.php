<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Administrator\Helper\FinderHelperLanguage;

HTMLHelper::_('bootstrap.popover');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$lang      = Factory::getLanguage();

Text::script('COM_FINDER_INDEX_CONFIRM_PURGE_PROMPT');
Text::script('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT');
HTMLHelper::_('script', 'com_finder/index.js', ['version' => 'auto', 'relative' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_finder&view=index'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<table class="table">
					<caption id="captionTable" class="sr-only">
						<?php echo Text::_('COM_FINDER_INDEX_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
					</caption>
					<thead>
						<tr>
							<td style="width:1%" class="text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" style="width:1%" class="text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'l.published', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'l.title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="width:10%" class="d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_INDEX_TYPE', 't.title', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="width:10%" class="d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_INDEX_DATE', 'l.indexdate', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" style="width:15%" class="text-center d-none d-md-table-cell text-center">
								<?php echo Text::_('COM_FINDER_INDEX_HEADING_DETAILS'); ?>
							</th>
							<th scope="col" style="width:30%" class="d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_FINDER_INDEX_HEADING_LINK_URL', 'l.url', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php $canChange = Factory::getUser()->authorise('core.manage', 'com_finder'); ?>
						<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="text-center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->link_id); ?>
							</td>
							<td class="text-center">
								<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'index.', $canChange, 'cb'); ?>
							</td>
							<th scope="row">
								<label for="cb<?php echo $i; ?>">
									<?php echo $this->escape($item->title); ?>
								</label>
							</th>
							<td class="small d-none d-md-table-cell text-center">
								<?php
								$key = FinderHelperLanguage::branchSingular($item->t_title);
								echo $lang->hasKey($key) ? Text::_($key) : $item->t_title;
								?>
							</td>
							<td class="small d-none d-md-table-cell text-center">
								<?php echo HTMLHelper::_('date', $item->indexdate, Text::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="text-center d-none d-md-table-cell text-center">
								<?php if ((int) $item->publish_start_date or (int) $item->publish_end_date or (int) $item->start_date or (int) $item->end_date) : ?>
									<span class="icon-calendar pop hasPopover" aria-hidden="true" data-placement="left" title="<?php echo Text::_('COM_FINDER_INDEX_DATE_INFO_TITLE'); ?>" data-content="<?php echo Text::sprintf('COM_FINDER_INDEX_DATE_INFO', $item->publish_start_date, $item->publish_end_date, $item->start_date, $item->end_date); ?>"></span>
									<span class="sr-only"><?php echo Text::sprintf('COM_FINDER_INDEX_DATE_INFO', $item->publish_start_date, $item->publish_end_date, $item->start_date, $item->end_date); ?></span>
								<?php endif; ?>
							</td>
							<td class="small break-word d-none d-md-table-cell">
								<?php echo (strlen($item->url) > 80) ? substr($item->url, 0, 70) . '...' : $item->url; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php // load the pagination. ?>
				<?php echo $this->pagination->getListFooter(); ?>

				<input type="hidden" name="task" value="display">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
