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

abstract class ePrivacyHelper {

    static function cookieTable($params) {
        if (!count((array)$params->get('cookies', array())) && !(bool) $params->get('sessioncookie', 0)) {
            return '';
        }
        $layout = JPluginHelper::getLayoutPath('system', 'eprivacy', 'table');
        ob_start();
        include $layout;
        $template = ob_get_contents();
        ob_end_clean();
        return $template;
    }

    static function legalLinks() {
        $lang = explode('-', JFactory::getLanguage()->getTag());
        $langtag = strtoupper($lang[0]);
        $linklang = 'EN';
        if (in_array($langtag, array('BG', 'ES', 'CS', 'DA', 'DE', 'ET', 'EL', 'EN', 'FR', 'GA', 'IT', 'LV', 'LT', 'HU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SL', 'FI', 'SV'))) {
            $linklang = $langtag;
        }
        $url = array(
            'https://eur-lex.europa.eu/LexUriServ/LexUriServ.do?uri=CELEX:32002L0058:' . $linklang . ':NOT',
            'https://eur-lex.europa.eu/legal-content/' . $linklang . '/TXT/HTML/?uri=CELEX:32016R0679'
        );
        return $url;
    }

    static function getPolicyURL($params) {
        $lang = JFactory::getLanguage()->getTag();
        $itemid = false;
        foreach ((array) $params->get('policy', array()) as $policy) {
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
        return $params->get('policyurl', false);
    }

    static function matchACL($params, $input=false) {
        $r = array();
        foreach ($params->get('cookies', array()) as $cookie) {
            if (!$input || $cookie->required || (is_array($input) && in_array((int)$cookie->acl, $input))) {
                $r[] = (int)$cookie->acl;
            }
        }
        return $r;
    }
    
    static function cookieExpires($max=false,$ltd=30) {
        $expires = new DateTime();
        $expires->add(new DateInterval('P'.$ltd.'D'));
        if($max) {
            $maxexpires = DateTime::createFromFormat('Y-m-d',$max);
            return $maxexpires>$expires?$expires->format('U'):$maxexpires->format('U');
        }
        return $expires->format('U');
    }
    
    static function cleanCookies($params) {
        $session = JFactory::getSession();
        $sessioncookie = $params->get('sessioncookie', false)?JFactory::getSession()->getName():false;
        if($sessioncookie) {
            $headers = headers_list();
        }
        header_remove('Set-Cookie');
        $app = JFactory::getApplication();
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $config = JFactory::getConfig();
            $cookiedomains = (array) $params->get('cookiedomains', array());
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                if (strlen(trim($cookie))) {
                    self::killCookie($sessioncookie, $params, $cookie, $cookiedomains, $app, $config);
                }
            }
        }
        if($sessioncookie) {
            foreach($headers as $header) {
                if(preg_match('/Set-Cookie/',$header)) {
                    $cookie = explode('; ',array_pop(explode('Set-Cookie: ',$header)));
                    $namevalue = array_shift($cookie);
                    list($name,$value) = explode('=',$namevalue);
                    if($session->getName() === $name) {
                        $args = array($name,$value);
                        $remainingargs = array();
                        array_map(function($i) use ($remainingargs) {
                            list($name,$value) = explode('=',$i);
                            $remainingargs[$name]=$value;
                        },$cookie);
                        $args[] = 0; // expire
                        if(isset($remainingargs['path'])) {
                            $args[] = $remainingargs['path'];
                        }
                        if(isset($remainingargs['domain'])) {
                            $args[] = $remainingargs['domain'];
                        }
                        $args[] = false; // secure
                        $args[] = true; // httponly
                        call_user_func_array('setcookie',$args);
                    }
                }
            }
        }
    }   

    static function killCookie($sessioncookie, $params, $cookie, $cookiedomains, $app, $config) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        if($name === $sessioncookie) {
            return;
        }
        $app->input->cookie->set($name, '', 0);
        $app->input->cookie->set($name, '', 0, $config->get('cookie_path', '/'), $config->get('cookie_domain', '.' . filter_input(INPUT_SERVER, 'HTTP_HOST')));
        if (count($cookiedomains)) {
            foreach ($cookiedomains as $o) {
                static::killOneCookie($name, $config->get('cookie_path', '/'), $o->domain);
            }
        }
    }
    
    static function killOneCookie($name,$path,$domain) {
        JFactory::getApplication()->input->cookie->set($name, '', 0, $path, $domain);        
    }
    
    static function encodeCookieValue($accepteddate,$maxdate,$acl = array()) {
        return implode('x',array('accepted'=>$accepteddate,'duration'=>$maxdate,'consent'=>implode('.',$acl)));
    }
    
    static function decodeCookieValue() {
        $cookie = JFactory::getApplication()->input->cookie->get('plg_system_eprivacy',false);
        if($cookie === false) {
            return array('accepted'=>false,'duration'=>false,'consent'=>array());
        }
        list($accepteddate,$maxdate,$acl) = explode('x',$cookie);
        return array('accepted'=>$accepteddate,'duration'=>$maxdate,'consent'=>explode('.',$acl));
    }
    
    static function getIP() {
        return getenv('HTTP_CLIENT_IP') ?:
                getenv('HTTP_X_FORWARDED_FOR') ?:
                getenv('HTTP_X_FORWARDED') ?:
                getenv('HTTP_FORWARDED_FOR') ?:
                getenv('HTTP_FORWARDED') ?:
                getenv('REMOTE_ADDR');
    }
    
    static function cookieRegex($data) {
        $levels = array();
        foreach((array)$data as $cookie) {
            if(!isset($levels[$cookie->acl])) {
                $levels[$cookie->acl] = array();
            }
            $levels[$cookie->acl][] = array('name'=>$cookie->name,'type'=>$cookie->type,'domain'=>$cookie->domain,'path'=>$cookie->path);
        }
        return $levels;
    }
    
}
