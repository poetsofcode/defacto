<?php

/**
 * @package plugin System - EU e-Privacy Directive
 * @copyright (C) 2010-2011 RicheyWeb - www.richeyweb.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * System - EU e-Privacy Directive Copyright (c) 2011 Michael Richey.
 * System - EU e-Privacy Directive is licensed under the http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
require_once __DIR__ . '/helper.php';

/**
 * ePrivacy system plugin
 */
class plgSystemePrivacy extends JPlugin {
    public $_cookie = false;
    public $_cookieACL;
    public $_noCookieACL;
    public $_defaultACL;
    public $_prep;
    public $_eprivacy;
    public $_clear;
    public $_country;
    public $_display;
    public $_displayed;
    public $_displaytype;
    public $_config;
    public $_exit;
    public $_eu;
    public $_app;
    public $_doc;
    public $_options;
    public $_layout = false;
    public $_params;

    public function __construct(&$subject) {
        parent::__construct($subject);
        if(JFactory::getApplication()->isAdmin()) {
            return;
        }
        $plugin = JPluginHelper::getPlugin('system','eprivacy');
        $this->params = new JRegistry();
        $this->params->loadString($plugin->params);
        $this->_pluginDefaults();
        $this->_cookie = ePrivacyHelper::decodeCookieValue();
        if ($this->_cookie['accepted'] !== false) {
            $acl = ePrivacyHelper::matchACL($this->params, $this->_cookie['consent']);
            $this->_display = false;
            $this->_eprivacy = true;
            $this->_addViewLevel($acl);
            $this->_loadPlugins($acl);
            return;
        } else {
            $acl = ePrivacyHelper::matchACL($this->params);
            $this->_addViewLevel($acl, true); // removing from all acl
        }
    }
    
    public function onAfterRoute() {
        if ($this->_exitEarly(true)) {
            return;
        }
        $app = JFactory::getApplication();

        // guests who have already accepted and have a cookie
        if ($this->_hasLongTermCookie()) {
            return;
        }


        // are they in a country where eprivacy is required?
        if ($this->params->get('geoplugin', false)) {
            $this->_useGeoPlugin();
        } else {
            $app->setUserState('plg_system_eprivacy_non_eu', false);
        }

        if (!$this->_eprivacy) {
            ePrivacyHelper::cleanCookies($this->params);
        }
        return true;
    }
    
    public function _pluginDefaults() {
        $config = JFactory::getConfig();
        $this->_defaultACL = (integer) $config->get('access', 1);
        $this->_cookieACL = (integer) $this->params->get('cookieACL', $this->_defaultACL);
        $this->_noCookieACL = (integer) $this->params->get('noCookieACL', $this->_defaultACL);
        $this->_prep = false;
        $this->_eprivacy = false;
        $this->_clear = array();
        $this->_country = false;
        $this->_display = true;
        $this->_displayed = false;
        $this->_displaytype = $this->params->get('displaytype', 'message');
        $this->_exit = false;
        $this->_eu = array(
            'localhost', // testing
            /* special cases - we run these just to be safe */
            'Anonymous Proxy', 'Satellite Provider',
            /* member states */
            'Austria', 'Belgium', 'Bulgaria', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 'Estonia', 'Finland', 'France', 'Germany',
            'Greece', 'Hungary', 'Ireland', 'Italy', 'Latvia', 'Lithuania', 'Luxembourg', 'Malta', 'Netherlands', 'Poland',
            'Portugal', 'Romania', 'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'United Kingdom',
            /* overseas member state territories */
            'Virgin Islands (BRITISH)'/* United Kingdom */,
            'French Guiana', 'Guadeloupe', 'Martinique', 'Reunion'/* France */,
            /* weirdness */
            'European Union'
        );
    }

    public function onBeforeCompileHead() {
        $this->_pagePrepJS($this->_displaytype, $this->_display);
        if ($this->_exitEarly()) {
            return true;
        }
        $this->_requestAccept();
        if (!$this->_eprivacy) {
            ePrivacyHelper::cleanCookies($this->params);
        }
        return true;
    }

    public function onBeforeRender() {
        if ($this->_exitEarly())
            return true;
        // because JAT3 is lame!
        $this->onBeforeCompileHead();
    }

    public function onAfterRender() {
        if ($this->_exitEarly())
            return true;
        if (!$this->_eprivacy) {
            ePrivacyHelper::cleanCookies($this->params);
        }
        if ($this->_layout) {
            $app = JFactory::getApplication();
            $body = $app->getBody();
            ob_start();
            include $this->_layout;
            $template = ob_get_contents();
            ob_end_clean();
            $app->setBody(str_replace('</body>', $template . "\n</body>", $body));
        }
        return true;
    }

    private function _requestAccept() {
        $app = JFactory::getApplication();
        switch ($this->params->get('displaytype', 'message')) {
            case 'message':
                $show = $app->input->cookie->get('plg_system_eprivacy_show',false);
                if (($this->_display && !$this->_displayed) || $show) {
                    $this->_displayed = true;
                    $msg = $this->_setMessage();
                    $app->enqueueMessage($msg, $this->params->get('messagetype', 'message'));
                }
                break;
            default:
                break;
        }
    }

    private function _getPolicyURL() {
        $lang = JFactory::getLanguage()->getTag();
        $itemid = false;
        foreach ((array) $this->params->get('policy', array()) as $policy) {
            if (!$itemid) {
                $itemid = $policy->itemid;
            }
            if ($policy->lang == $lang) {
                $itemid = $policy->itemid;
            }
        }
        if ($itemid) {
            $menu = JFactory::getApplication()->getMenu();
            $link = $menu->getItem($itemid)->link;
            return JRoute::_($link . '&Itemid=' . $itemid, false);
        }
        return $this->params->get('policyurl', '');
    }

//    private function _prepCookieDefinitions() {
//        $defs = array();
//        foreach($this->params->get('cookies',array()) as $cookie) {
//            $defs[] = array(
//                $cookie->domain,
//                preg_match('/PLG_SYS_EPRIVACY_COOKIENAME_/',$cookie->name)?JText::_($cookie->name):$cookie->name,
//                preg_match('/PLG_SYS_EPRIVACY_COOKIEDESC_/',$cookie->desc)?JText::_($cookie->desc):$cookie->desc,
//                (bool)$cookie->required,
//                (int)$cookie->acl
//            );
//        }
//        return $defs;
//    }


    private function _pagePrepJS($type, $autoopen = true) {
        $app = JFactory::getApplication();
        if ($app->isAdmin() || $this->_prep) {
            return;
        }
        $doc = JFactory::getDocument();
        $config = JFactory::getConfig();
        $min = $config->get('debug', false) ? '' : '.min';
        JHtml::_('jquery.framework', true, true);
        $scriptoptions = version_compare(JVERSION, '3.7.0', 'lt') ? 'text/javascript' : array('version' => 'auto');
        $doc->addStyleSheet(JURI::root(true) . '/media/plg_system_eprivacy/css/definitions' . $min . '.css', array('version' => 'auto'));
        $doc->addScript(JURI::root(true) . '/media/plg_system_eprivacy/js/base64.min.js', $scriptoptions);
        $doc->addScript(JURI::root(true) . '/media/plg_system_eprivacy/js/eprivacy.class' . $min . '.js', $scriptoptions);
        $this->loadLanguage('plg_system_eprivacy');
        $options = array(
            'displaytype' => $type,
            'autoopen' => in_array($autoopen, array('modal', 'confirm')),
            'accepted' => ($this->_eprivacy ? true : false),
            'root' => JURI::root(true)
        );
        $options['root'] .= ((substr($options['root'], -1) == '/') ? '' : '/') . 'index.php';
        $cookie_domain = $config->get('cookie_domain', '');
        $cookie_path = $config->get('cookie_path', '');
        $policyurl = ePrivacyHelper::getPolicyURL($this->params);
        $options['cookies'] = array(
            'sessioncookie' => (bool) $this->params->get('sessioncookie', false)
        );
        $this->_cookie = $this->_cookie?:ePrivacyHelper::decodeCookieValue();
        $options['cookies']['accepted'] = $options['accepted'] ? ePrivacyHelper::matchACL($this->params, $this->_cookie['consent']) : array();
        $options['cookie'] = array(
            'domain' => (strlen(trim($cookie_domain)) > 0) ? $cookie_domain : '.' . filter_input(INPUT_SERVER, 'HTTP_HOST'),
            'path' => strlen($cookie_path) ? $cookie_path : null,
        );
        $options['loginlinks'] = array_values((array) $this->params->get('loginlinks', array()));
        $options['country'] = $this->params->get('geoplugin',false)?$this->_country:'not detected';
        if (in_array($type, array('message', 'confirm', 'module', 'modal', 'ribbon'))) {
            $this->_getCSS('module');
            $this->_jsStrings($type);
        }
        $options['cookieregex'] = ePrivacyHelper::cookieRegex($this->params->get('cookieregex',array()));
        switch ($type) {
            case 'message':
            case 'confirm':
            case 'module':
                break;
            case 'modal':
                $this->_layout = JPluginHelper::getLayoutPath('system', 'eprivacy', 'modal');
                break;
            case 'ribbon':
                $this->_getCSS('ribbon', $min);
                $options['policyurl'] = $policyurl;
                $options['policytarget'] = $this->params->get('policytarget', '_blank');
                $options['agreeclass'] = $this->params->get('ribbonagreeclass', '');
                $options['declineclass'] = $this->params->get('ribbondeclineclass', '');
                if ($this->params->get('lawlink', 1)) {
                    $options['lawlink'] = ePrivacyHelper::legalLinks();
                } else {
                    $options['lawlink'] = array();
                }
                $this->_options = $options;
                $this->_layout = JPluginHelper::getLayoutPath('system', 'eprivacy');
                break;
            case 'cookieblocker';
                break;
        }
        if ($type === 'cookieblocker') {
            $doc->addStyleDeclaration("\n#plg_system_eprivacy { width:0px;height:0px;clear:none; BEHAVIOR: url(#default#userdata); }\n");
        }
        $doc->addScriptOptions('plg_system_eprivacy', $options);
        $this->_prep = true;
    }

    private function _setMessage() {
        $msg = '<div class="plg_system_eprivacy_message">';
        $msg .= '<h2>' . JText::_('PLG_SYS_EPRIVACY_MESSAGE_TITLE') . '</h2>';
        $msg .= '<p>' . JText::_('PLG_SYS_EPRIVACY_MESSAGE') . '</p>';
        $policyurl = ePrivacyHelper::getPolicyURL($this->params);
        if (strlen(trim($policyurl))) {
            $msg .= '<p><a href="' . trim($policyurl) . '" target="' . $this->params->get('policytarget', '_blank') . '">' . JText::_('PLG_SYS_EPRIVACY_POLICYTEXT') . '</a></p>';
        }
        if ($this->params->get('lawlink', 1)) {
            $links = ePrivacyHelper::legalLinks();
            $msg .= '<p><a href="' . $links[0] . '" onclick="window.open(this.href);return false;">' . JText::_('PLG_SYS_EPRIVACY_LAWLINK_TEXT') . '</a></p>';
            $msg .= '<p><a href="' . $links[1] . '" onclick="window.open(this.href);return false;">' . JText::_('PLG_SYS_EPRIVACY_GDPRLINK_TEXT') . '</a></p>';
        }
        $msg .= ePrivacyHelper::cookieTable($this->params);
        $msg .= '<button class="plg_system_eprivacy_agreed">' . JText::_('PLG_SYS_EPRIVACY_AGREE') . '</button>';
        $msg .= '<button class="plg_system_eprivacy_declined">' . JText::_('PLG_SYS_EPRIVACY_DECLINE') . '</button>';
        $msg .= '<div id="plg_system_eprivacy"></div>';
        $msg .= '</div>';
        $msg .= '<div class="plg_system_eprivacy_declined">';
        $msg .= JText::_('PLG_SYS_EPRIVACY_DECLINED');
        $msg .= '<button class="plg_system_eprivacy_reconsider">' . JText::_('PLG_SYS_EPRIVACY_RECONSIDER') . '</button>';
        $msg .= '</div>';
        return $msg;
    }

    private function _useGeoPlugin() {
        if (class_exists('ePrivacyGeoIPHelper')) {
            $ip = ePrivacyHelper::getIP();
            $geoiphelper = ePrivacyGeoIPHelper::getInstance();
            $this->_country = $geoiphelper->lookupName($ip);
            if($this->_country === false || !in_array($this->_country,$this->_eu)) {
                $this->_eprivacy = true;
                $this->_display = false;
                $acl = ePrivacyHelper::matchACL($this->params);
                $this->_addViewLevel($acl);
                JFactory::getApplication()->setUserState('plg_system_eprivacy', $acl);
                JFactory::getApplication()->setUserState('plg_system_eprivacy_non_eu', true);
            } else {
                $this->_eprivacy = false;
                error_log(print_r($this->_cookie,true));
                $this->_display = true;
                JFactory::getApplication()->setUserState('plg_system_eprivacy_non_eu', false);
            }
        } else {
            error_log('The Geo IP plugin was not found.');
            $this->_eprivacy = false;
            $this->_country = 'Geo IP Country lookup not available';
            $this->_display = true;
        }
    }

    private function _jsStrings($type) {
        JText::script('PLG_SYS_EPRIVACY_JSMESSAGE');
        JText::script('PLG_SYS_EPRIVACY_MESSAGE');
        JText::script('PLG_SYS_EPRIVACY_TH_COOKIENAME');
        JText::script('PLG_SYS_EPRIVACY_TH_COOKIEDOMAIN');
        JText::script('PLG_SYS_EPRIVACY_TH_COOKIEDESCRIPTION');
        JText::script('PLG_SYS_EPRIVACY_TD_SESSIONCOOKIE');
        JText::script('PLG_SYS_EPRIVACY_TD_SESSIONCOOKIE_DESC');
        $strings = array(
            'message' => array('CONFIRMUNACCEPT'),
            'module' => array('CONFIRMUNACCEPT'),
            'modal' => array('MESSAGE_TITLE', 'POLICYTEXT', 'LAWLINK_TEXT', 'AGREE', 'DECLINE', 'CONFIRMUNACCEPT'),
            'confirm' => array('CONFIRMUNACCEPT'),
            'ribbon' => array('POLICYTEXT', 'LAWLINK_TEXT', 'GDPRLINK_TEXT', 'AGREE', 'DECLINE', 'CONFIRMUNACCEPT')
        );
        foreach ($strings[$type] as $string) {
            JText::script('PLG_SYS_EPRIVACY_' . $string);
        }
    }

    private function _exitEarly($initialise = false) {
        if ($this->_exit) {
            return true;
        }
        $app = JFactory::getApplication();

        // plugin should only run in the front-end
        if ($app->isAdmin()) {
            $this->_exit = true;
            return true;
        }

        // shouldn't run in raw output
        if ($app->input->get('format', '', 'cmd') == 'raw') {
            $this->_exit = true;
            
            if ($app->input->cookie->get('plg_system_eprivacy', false) === false) {
                ePrivacyHelper::cleanCookies($this->params);
            }
        
            return true;
        }
                
        if ($this->params->get('geoplugin', false)) {
            if (class_exists('ePrivacyGeoIPHelper')) {
                $ip = ePrivacyHelper::getIP();
                $geoiphelper = ePrivacyGeoIPHelper::getInstance();
                $this->_country = $geoiphelper->lookupName($ip);
                if($this->_country && !in_array($this->_country,$this->_eu)) {
                    $acl = ePrivacyHelper::matchACL($this->params);
                    $this->_addViewLevel($acl);
                    $this->_exit = true;
                    return true;
                }
            }
        }
        
        // don't interfere with ajax calls
        if ($app->input->getCmd('option', false) === 'com_ajax') {
            $this->_exit = true;
            return true;
        }
        
        // plugin should only run in HTML pages
        $doc = JFactory::getDocument();
        if (!$initialise) {
            if ($doc->getType() != 'html') {
                $this->_exit = true;
                return true;
            }
        }
        
        if ($app->input->get('tmpl', false) === 'component') {
            return true;
        }

        return false;
    }

    private function _hasLongTermCookie() {        
        $app = JFactory::getApplication();
        if ($this->params->get('longtermcookie', false)) {
            $this->_cookie = $this->_cookie?:ePrivacyHelper::decodeCookieValue();
            if ($this->_cookie['accepted'] !== false) {
                if(!$app->getUserState('plg_system_eprivacy',false)) {
                    $app->setUserState('plg_system_eprivacy',$this->_cookie['consent']);
                }
                $config = JFactory::getConfig();
                $this->_addViewLevel($this->_cookie['consent']);
                $this->_eprivacy = true;
                $this->_display = false;
                $cookie_path = $config->get('cookie_path', '/');
                $cookie_domain = $config->get('cookie_domain', '.' . filter_input(INPUT_SERVER, 'HTTP_HOST'));
                $expire = ePrivacyHelper::cookieExpires(false, $this->params->get('longtermcookieduration', 30));
                $value = ePrivacyHelper::encodeCookieValue($this->_cookie['accepted'], $this->_cookie['duration'], $this->_cookie['consent']);
                $app->input->cookie->set('plg_system_eprivacy', $value, $expire, $cookie_path, $cookie_domain);
                return true;
            }
        }
        return false;
    }

    private function _reflectJUser($acl, $remove = false) {
        if ((int) $acl === $this->_defaultACL) {
            return;
        }
        $user = JFactory::getUser();
        $JAccessReflection = new ReflectionClass('JUser');
        $_authLevels = $JAccessReflection->getProperty('_authLevels');
        $_authLevels->setAccessible(true);
        $groups = $_authLevels->getValue($user);
        switch ($remove) {
            case true:
                $key = array_search((int) $acl, $groups);
                if ($key) {
                    unset($groups[$key]);
                }
                break;
            default:
                if (!array_search((int) $acl, $groups)) {
                    $groups[] = (int) $acl;
                }
                break;
        }
        $_authLevels->setValue($user, $groups);
    }

    private function _addViewLevel($acl, $remove = false) {        
        if (!class_exists('ReflectionClass', false) || !method_exists('ReflectionProperty', 'setAccessible'))
            return;
        if ($this->_defaultACL == $this->_cookieACL || $this->_defaultACL == $this->_noCookieACL)
            return;
        $this->_reflectJUser($this->_cookieACL, $remove);
        $this->_reflectJUser($this->_noCookieACL, !$remove);
        foreach ($acl as $levelid) {
            $this->_reflectJUser((int) $levelid, $remove);
        }        
    }

    private function _getCSS($type, $min = '.min') {
        $doc = JFactory::getDocument();
        switch ($type) {
            case 'ribbon':
                if ($this->params->get('useribboncss', 1)) {
                    $doc->addStyleSheet(JURI::root(true) . '/media/plg_system_eprivacy/css/ribbon' . $min . '.css', array('version' => 'auto'));
                    $doc->addStyleDeclaration($this->params->get('ribboncss'));
                }
                break;
            case 'module':
                if ($this->params->get('usemodulecss', 1)) {
                    $doc->addStyleDeclaration($this->params->get('modulecss'));
                }
                break;
            default:
                break;
        }
    }
    
    private function _loadPlugins($acl) {
        $levels = array_filter( $acl, 'strlen' );
        if(!count($levels)) {
            return;
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('folder,element')->from('#__extensions')->where('enabled = 1')->where('access IN ('.implode(',',$levels).')')->order('folder ASC');
        try {
            $db->transactionStart();
            $db->setQuery($query);
            $result = $db->loadObjectList();
            $db->transactionCommit();
        }
        catch (Exception $e){
            $db->transactionRollback();
            JErrorPage::render($e);
        }
        if($result) {
            $reflection = new ReflectionProperty('JPluginHelper','plugins');
            $reflection->setAccessible(true);
            $reflection->setValue(null,null);
            $dispatcher = JDispatcher::getInstance();
            foreach($result as $plugin) {
                $loaded = JPluginHelper::importPlugin($plugin->folder, $plugin->element, true, $dispatcher);
                if(!$loaded) {
                    error_log('eprivacy was unable to load '.$plugin->folder.'/'.$plugin->element);
                }
            }
        }
    }

}
