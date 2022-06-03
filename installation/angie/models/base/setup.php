<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

abstract class AngieModelBaseSetup extends AModel
{
    /**
     * Cached copy of the configuration model
     *
     * @var  AngieModelWordpressConfiguration
     */
    protected $configModel = null;

    /**
     * Overridden constructor
     *
     * @param   array       $config     Configuration array
     * @param   \AContainer $container
     */
    public function __construct($config = [], AContainer $container = null)
    {
        parent::__construct($config, $container);

        $this->configModel = AModel::getAnInstance('Configuration', 'AngieModel', [], $this->container);
    }

    /**
     * Return an object containing the configuration variables we read from the
     * state or the request.
     *
     * @return  stdClass
     */
    public function getStateVariables()
    {
        static $params = [];

        if(empty($params))
        {
            $params = array_merge($params, $this->getSiteParamsVars());
            $params = array_merge($params, $this->getSuperUsersVars());
        }

        return (object) $params;
    }

    abstract protected function getSiteParamsVars();

    abstract protected function getSuperUsersVars();

    abstract public function applySettings();

	/**
	 * Are we restoring to a new host?
	 *
	 * @return bool
	 */
	public function isNewhost()
	{
		/** @var AngieModelBaseMain $mainModel */
		$mainModel = AModel::getAnInstance('Main', 'AngieModel');
		$extrainfo = $mainModel->getExtraInfo();

		if (isset($extrainfo['host']))
		{
			$uri = AUri::getInstance();

			if ($extrainfo['host']['current'] != $uri->getHost())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Are we restoring to a different filesystem?
	 *
	 * @return bool
	 */
	public function isDifferentFilesystem()
	{
		/** @var AngieModelBaseMain $mainModel */
		$mainModel = AModel::getAnInstance('Main', 'AngieModel');
		$extrainfo = $mainModel->getExtraInfo();

		if (isset($extrainfo['root']))
		{
			// Trim any trailing slashes to be sure
			$old_path = rtrim($extrainfo['root']['current'],'/\\');
			$new_path = rtrim(APATH_ROOT, '/\\');

			if ($old_path != $new_path)
			{
				return true;
			}
		}

		return false;
	}

    /**
     * Returns the database connection variables for the default database.
     *
     * @return null|stdClass
     */
    protected function getDbConnectionVars()
    {
        /** @var AngieModelDatabase $model */
        $model		 = AModel::getAnInstance('Database', 'AngieModel', [], $this->container);
        $keys		 = $model->getDatabaseNames();
        $firstDbKey	 = array_shift($keys);

        return $model->getDatabaseInfo($firstDbKey);
    }

    /**
     * Shorthand method to get the connection to the current database
     *
     * @return ADatabaseDriver
     */
    protected function getDatabase()
    {
        $connectionVars = $this->getDbConnectionVars();
        $name           = $connectionVars->dbtype;

        $options = [
            'database'	 => $connectionVars->dbname,
            'select'	 => 1,
            'host'		 => $connectionVars->dbhost,
            'user'		 => $connectionVars->dbuser,
            'password'	 => $connectionVars->dbpass,
            'prefix'	 => $connectionVars->prefix,
        ];

        $db	= ADatabaseFactory::getInstance()->getDriver($name, $options);

        return $db;
    }
}
