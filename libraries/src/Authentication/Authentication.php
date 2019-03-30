<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;

/**
 * Authentication class, provides an interface for the Joomla authentication system
 *
 * @since  1.7.0
 */
class Authentication
{
	use DispatcherAwareTrait;

	/**
	 * This is the status code returned when the authentication is success (permit login)
	 * @const  STATUS_SUCCESS successful response
	 * @since  1.7.0
	 */
	const STATUS_SUCCESS = 1;

	/**
	 * Status to indicate cancellation of authentication (unused)
	 * @const  STATUS_CANCEL cancelled request (unused)
	 * @since  1.7.0
	 */
	const STATUS_CANCEL = 2;

	/**
	 * This is the status code returned when the authentication failed (prevent login if no success)
	 * @const  STATUS_FAILURE failed request
	 * @since  1.7.0
	 */
	const STATUS_FAILURE = 4;

	/**
	 * This is the status code returned when the account has expired (prevent login)
	 * @const  STATUS_EXPIRED an expired account (will prevent login)
	 * @since  1.7.0
	 */
	const STATUS_EXPIRED = 8;

	/**
	 * This is the status code returned when the account has been denied (prevent login)
	 * @const  STATUS_DENIED denied request (will prevent login)
	 * @since  1.7.0
	 */
	const STATUS_DENIED = 16;

	/**
	 * This is the status code returned when the account doesn't exist (not an error)
	 * @const  STATUS_UNKNOWN unknown account (won't permit or prevent login)
	 * @since  1.7.0
	 */
	const STATUS_UNKNOWN = 32;

	/**
	 * @var    Authentication[]  JAuthentication instances container.
	 * @since  1.7.3
	 */
	protected static $instance = [];

	/**
	 * Plugin Type to run
	 *
	 * @type   string
	 * @since  4.0.0
	 */
	protected $pluginType;

	/**
	 * Constructor
	 *
	 * @param   string               $pluginType  The plugin type to run authorisation and authentication on
	 * @param   DispatcherInterface  $dispatcher  The event dispatcher we're going to use
	 *
	 * @since   1.7.0
	 */
	public function __construct(string $pluginType = 'authentication', DispatcherInterface $dispatcher = null)
	{
		// Set the dispatcher
		if (!is_object($dispatcher))
		{
			$dispatcher = Factory::getContainer()->get('dispatcher');
		}

		$this->setDispatcher($dispatcher);
		$this->pluginType = $pluginType;

		$isLoaded = PluginHelper::importPlugin($this->pluginType);

		if (!$isLoaded)
		{
			Log::add(Text::_('JLIB_USER_ERROR_AUTHENTICATION_LIBRARIES'), Log::WARNING, 'jerror');
		}
	}

	/**
	 * Returns the global authentication object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $pluginType  The plugin type to run authorisation and authentication on
	 *
	 * @return  Authentication  The global Authentication object
	 *
	 * @since   1.7.0
	 */
	public static function getInstance(string $pluginType = 'authentication')
	{
		if (empty(self::$instance[$pluginType]))
		{
			self::$instance[$pluginType] = new static($pluginType);
		}

		return self::$instance[$pluginType];
	}

	/**
	 * Finds out if a set of login credentials are valid by asking all observing
	 * objects to run their respective authentication routines.
	 *
	 * @param   array  $credentials  Array holding the user credentials.
	 * @param   array  $options      Array holding user options.
	 *
	 * @return  AuthenticationResponse  Response object with status variable filled in for last plugin or first successful plugin.
	 *
	 * @see     AuthenticationResponse
	 * @since   1.7.0
	 */
	public function authenticate($credentials, $options = array())
	{
		// Get plugins
		$plugins = PluginHelper::getPlugin($this->pluginType);

		// Create authentication response
		$response = new AuthenticationResponse;

		// Get the dispatcher
		$dispatcher = $this->getDispatcher();

		/*
		 * Loop through the plugins and check if the credentials can be used to authenticate
		 * the user
		 *
		 * Any errors raised in the plugin should be returned via the AuthenticationResponse
		 * and handled appropriately.
		 */
		foreach ($plugins as $plugin)
		{
			$className = 'plg' . str_replace('-', '', $plugin->type) . $plugin->name;

			if (class_exists($className))
			{
				$plugin = new $className($dispatcher, (array) $plugin);
			}
			else
			{
				// Bail here if the plugin can't be created
				Log::add(Text::sprintf('JLIB_USER_ERROR_AUTHENTICATION_FAILED_LOAD_PLUGIN', $className), Log::WARNING, 'jerror');
				continue;
			}

			// Try to authenticate
			$plugin->onUserAuthenticate($credentials, $options, $response);

			// If authentication is successful break out of the loop
			if ($response->status === self::STATUS_SUCCESS)
			{
				if (empty($response->type))
				{
					$response->type = $plugin->_name ?? $plugin->name;
				}

				break;
			}
		}

		if (empty($response->username))
		{
			$response->username = $credentials['username'];
		}

		if (empty($response->fullname))
		{
			$response->fullname = $credentials['username'];
		}

		if (empty($response->password) && isset($credentials['password']))
		{
			$response->password = $credentials['password'];
		}

		return $response;
	}

	/**
	 * Authorises that a particular user should be able to login
	 *
	 * @param   AuthenticationResponse  $response  response including username of the user to authorise
	 * @param   array                   $options   list of options
	 *
	 * @return  AuthenticationResponse[]  Array of authentication response objects
	 *
	 * @since  1.7.0
	 * @throws \Exception
	 */
	public function authorise($response, $options = array())
	{
		// Get plugins in case they haven't been imported already
		PluginHelper::importPlugin('user');
		$results = Factory::getApplication()->triggerEvent('onUserAuthorisation', array($response, $options));

		return $results;
	}
}
