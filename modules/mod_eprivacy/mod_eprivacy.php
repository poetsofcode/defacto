<?php

/**
 * @package		Joomla.Site
 * @subpackage	mod_footer
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');
require_once JPATH_PLUGINS.'/system/eprivacy/helper.php';
$app = JFactory::getApplication();
$dispatcher = JDispatcher::getInstance();
JPluginHelper::importPlugin('system', 'eprivacy', true, $dispatcher);
$plugin = JPluginHelper::getPlugin('system', 'eprivacy');
$pluginparams = new JRegistry();
$pluginparams->loadString(is_object($plugin)?$plugin->params:'{}');
$policyurl = ePrivacyHelper::getPolicyURL($pluginparams);
if (!$app->getUserState('plg_system_eprivacy_non_eu', false)) {
    $lang = JFactory::getLanguage();

    $lang->load('plg_system_eprivacy', JPATH_ADMINISTRATOR);
    $legallinks = $pluginparams->get('lawlink', 1)?ePrivacyHelper::legalLinks():false;
    $uri = $_SERVER['REQUEST_URI'];
    $query_string = explode('&', $_SERVER['QUERY_STRING']);
    if (count($query_string) && strlen($query_string[0])) {
        $uri .= '&eprivacy=1';
    } else {
        $uri .= '?eprivacy=1';
    }
//    switch($pluginparams->get('displaytype','message')) {
//        case 'module':
//            $reconsider='plg_system_eprivacy_showmessage();';
//            break;
//        case 'modal':
//            $reconsider.='plg_system_eprivacy_showmessage();plg_system_eprivacy_modalIt(\''.plgSystemePrivacy::_getURI().'\');';
//            break;
//    }
    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
    require JModuleHelper::getLayoutPath('mod_eprivacy', $params->get('layout', 'default'));
}