<?php

/**
 * @package     EU e-Privacy Directive
 * @copyright   Copyright (C) 2009 - 2014 Michael Richey. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_PLUGINS.'/system/eprivacy/helper.php';

class plgAjaxEprivacy extends JPlugin {

	public function onAjaxEprivacy() {
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$method = $this->input->get('method', 'error');
		if(!method_exists($this,'_'.$method))
		{
			return $this->_error();
		}
		
		$userparams = JComponentHelper::getParams('com_users');
		$sysparams = $this->sysParams();		
		return json_encode($this->{'_' . $method}($sysparams)? : $this->error());
	}

	// exposed methods globally available are prefixed with a single underscore
	private function _error() {
		return false;
	}
	
	private function _accept($sysparams) {
		$config = JFactory::getConfig();
                $consent = explode('.',$this->input->get->get('consent',array(),'STRING'));
                $addacl = ePrivacyHelper::matchACL($sysparams,$consent);
                
                // cookie
		$name = 'plg_system_eprivacy';
                $now = new DateTime();
                $accepteddate = $now->format('Y-m-d');
                $maxdate = $now->add(new DateInterval('P6M'))->format('Y-m-d');
                $expires = ePrivacyHelper::cookieExpires(false,$this->params->get('longtermcookieduration', 30));
		$path = strlen($config->get('cookie_path')) ? $config->get('cookie_path') : '/';
		$domain = strlen($config->get('cookie_domain')) ? $config->get('cookie_domain') : $this->input->server->get('HTTP_HOST');
		$this->input->cookie->set($name, ePrivacyHelper::encodeCookieValue($accepteddate, $maxdate, $addacl), $expires, $path, $domain, false, false);
                if($this->input->cookie->get('plg_system_eprivacy_show',false)) {
                    ePrivacyHelper::killOneCookie('plg_system_eprivacy_show', $path, $domain);
                }
		if ($sysparams->get('logaccept', false))
		{
			$o = new stdClass();
                        $o->ip = ePrivacyHelper::getIP();
			$o->state = $this->input->get('country','not detected');
			$o->accepted = JFactory::getDate()->toSql();
			JFactory::getDbo()->insertObject('#__plg_system_eprivacy_log',$o);
		} else {
			$this->app->setUserState('plg_system_eprivacy_non_eu', false);
		}
		$this->app->setUserState('plg_system_eprivacy', $addacl);	
		return true;
	}

	private function _decline($sysparams) {
                ePrivacyHelper::cleanCookies($sysparams);
		$app = JFactory::getApplication();
		$app->setUserState('plg_system_eprivacy', false);
		return true;
	}

	// really private methods have no underscore
	private function sysParams() {
		$plugin = JPluginHelper::getPlugin('system','eprivacy');
		$params = new JRegistry($plugin->params);
		return $params;
	}
}
