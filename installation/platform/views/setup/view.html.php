<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

class AngieViewSetup extends AView
{
	public $stateVars   =   null;
	public $hasFTP      =   true;

	/**
	 * Are we running under Apache webserver?
	 *
	 * @var bool
	 */
	public $htaccessSupported  =   false;

	/**
	 * Are we running under NGINX webserver?
	 *
	 * @var bool
	 */
	public $nginxSupported     =   false;

	/**
	 * Are we running under IIS webserver?
	 *
	 * @var bool
	 */
	public $webConfSupported   =   false;

	public $removePhpiniOptions     = [];
	public $removeAddHandlerOptions = [];
	public $replaceHtaccessOptions  = [];
	public $replaceWeconfigOptions  = [];
	public $removeHtpasswdOptions   = [];

	/**
	 * Are we restoring under HTTP but we have the option Force SSL enabled? If so print the warning
	 *
	 * @var bool
	 */
	public $protocolMismatch = false;

	/**
	 * @return bool
	 */
	public function onBeforeMain()
	{
		/** @var AngieModelJoomlaSetup $model */
		$model           = $this->getModel();
		$this->stateVars = $model->getStateVariables();
		$this->hasFTP    = function_exists('ftp_connect');

		$this->htaccessSupported = AUtilsServertechnology::isHtaccessSupported();
		$this->nginxSupported    = AUtilsServertechnology::isNginxSupported();
		$this->webConfSupported  = AUtilsServertechnology::isWebConfigSupported();

		// Prime the options array with some default info
		$this->removePhpiniOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEPHPINI_HELP'
		];

		$this->removeAddHandlerOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEADDHANDLER_HELP'
		];

		$this->replaceHtaccessOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REPLACEHTACCESS_HELP'
		];

		$this->replaceWeconfigOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REPLACEWEBCONFIG_HELP'
		];

		$this->removeHtpasswdOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEHTPASSWD_HELP'
		];

		// If we are restoring to a new server everything is checked by default
		if ($model->isNewhost())
		{
			$this->removePhpiniOptions['checked']    = 'checked="checked"';
			$this->replaceHtaccessOptions['checked'] = 'checked="checked"';
			$this->replaceWeconfigOptions['checked'] = 'checked="checked"';
			$this->removeHtpasswdOptions['checked']  = 'checked="checked"';
		}

		// Special case for AddHandler rule: we want to show that if it's a new host OR the file path is different
		if ($model->isNewhost() || $model->isDifferentFilesystem())
		{
			$this->removeAddHandlerOptions['checked'] = 'checked="checked"';
		}

		// If any option is not valid (ie missing files) we gray out the option AND remove the check
		// to avoid user confusion
		if (!$model->hasPhpIni())
		{
			$this->removePhpiniOptions['disabled']   = 'disabled="disabled"';
			$this->removePhpiniOptions['checked']    = '';
			$this->removePhpiniOptions['help']       = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasHtaccess())
		{
			$this->replaceHtaccessOptions['disabled'] = 'disabled="disabled"';
			$this->replaceHtaccessOptions['checked']  = '';
			$this->replaceHtaccessOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasWebconfig())
		{
			$this->replaceWeconfigOptions['disabled'] = 'disabled="disabled"';
			$this->replaceWeconfigOptions['checked']  = '';
			$this->replaceWeconfigOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasHtpasswd())
		{
			$this->removeHtpasswdOptions['disabled'] = 'disabled="disabled"';
			$this->removeHtpasswdOptions['checked']  = '';
			$this->removeHtpasswdOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasAddHandler())
		{
			$this->removeAddHandlerOptions['disabled'] = 'disabled="disabled"';
			$this->removeAddHandlerOptions['checked']  = '';
			$this->removeAddHandlerOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		$this->protocolMismatch = $model->protocolMismatch();

		return true;
	}
}
