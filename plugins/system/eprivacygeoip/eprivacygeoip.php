<?php

/**
 * @copyright   Copyright (C) 2006 - 2018 Michael Richey. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

class PlgSystemePrivacyGeoIP extends JPlugin {

    public function __construct(&$subject, $config = array()) {
        parent::__construct($subject, $config);
        $cache = JFactory::getApplication()->getUserState('plg_system_eprivacygeoip_cache',false);
        $geoiphelper = ePrivacyGeoIPHelper::getInstance($cache,$this->params);
        $ip = $geoiphelper->getIP();
        $ipinfo = $geoiphelper->all($ip);
        if(!$cache) {
            JFactory::getApplication()->setUserState('plg_system_eprivacygeoip_cache',$ipinfo);
        }
    }

}
