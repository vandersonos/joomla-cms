<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Extension table
 *
 * @since  1.7.0
 */
class Extension extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database driver object.
	 *
	 * @since   1.7.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__extensions', 'extension_id', $db);

		// Set the alias since the column is called enabled
		$this->setColumnAlias('published', 'enabled');
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True if the object is ok
	 *
	 * @see     Table::check()
	 * @since   1.7.0
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Check for valid name
		if (trim($this->name) == '' || trim($this->element) == '')
		{
			$this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_EXTENSION'));

			return false;
		}

		return true;
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 * to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error
	 *
	 * @see     Table::bind()
	 * @since   1.7.0
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && \is_array($array['params']))
		{
			$registry = new Registry($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['control']) && \is_array($array['control']))
		{
			$registry = new Registry($array['control']);
			$array['control'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to create and execute a SELECT WHERE query.
	 *
	 * @param   array  $options  Array of options
	 *
	 * @return  string  The database query result
	 *
	 * @since   1.7.0
	 */
	public function find($options = array())
	{
		// Get the DatabaseQuery object
		$query = $this->_db->getQuery(true);

		// Get the column field types for the #__extensions Table
		$tableColumnsType = $this->_db->getTableColumns('#__extensions', false);

		foreach ($options as $col => $val)
		{
			if (\in_array($col, (array) $tableColumnsType[$col]))
			{
				// Mysql and Postgresql have different properties name for a column data
				$type = isset($tableColumnsType[$col]->Type) ? $tableColumnsType[$col]->Type : $tableColumnsType[$col]->type;

				// MCD for different field types on different databases for the #__extensions Table
				switch (trim(substr($type, 0, 7)))
				{
					case 'varchar':
					case 'charact':
					case 'text':
					case 'timesta':
					case 'datetim':
						$val = (!empty($val)) ? $val : null;
						$query->where($this->_db->quoteName($col) . ' = :' . $col)
							->bind(':' . $col, $val);
						break;

					case 'int':
					case 'int uns':
					case 'tinyint':
					case 'integer':
					case 'smallin':
					case 'bigint':
						$val = intval($val);
						$query->where($this->_db->quoteName($col) . ' = :' . $col)
							->bind(':' . $col, $val, ParameterType::INTEGER);
						break;
				}
			}
		}

		$query->select($this->_db->quoteName('extension_id'))
			->from($this->_db->quoteName('#__extensions'));
		$this->_db->setQuery($query);

		return $this->_db->loadResult();
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k       = $this->_tbl_key;
		$checkin = false;

		// Sanitize input.
		$pks = ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true)
			->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('enabled') . ' = :state')
			->whereIn($this->_db->quoteName($k), $pks)
			->bind(':state', $state, ParameterType::INTEGER);

		// Determine if there is checkin support for the table.
		if ($this->hasField('checked_out') && $this->hasField('checked_out_time'))
		{
			$checkin = true;
			$query->extendWhere(
				'AND',
				[
					$this->_db->quoteName('checked_out') . ' = 0',
					$this->_db->quoteName('checked_out') . ' = :checked_out',
				],
				'OR'
			)
				->bind(':checked_out', $userId, ParameterType::INTEGER);
		}

		$this->_db->setQuery($query);
		$this->_db->execute();

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (\count($pks) == $this->_db->getAffectedRows()))
		{
			// Checkin the rows.
			foreach ($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the Table instance value is in the list of primary keys that were set, set the instance.
		if (\in_array($this->$k, $pks))
		{
			$this->enabled = $state;
		}

		$this->setError('');

		return true;
	}
}
