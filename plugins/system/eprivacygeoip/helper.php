<?php

/**
 * @copyright   Copyright (C) 2006 - 2018 Michael Richey. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class ePrivacyGeoIPHelper {

    protected static $instance;
    private $params;
    private $loaded = false;
    private $cache = array(
        'cc' => array(),
        'abbr' => array(),
        'name' => array()
    );

    final public static function getInstance($cache = false, $params = false) {
        if (!isset(static::$instance)) {
            static::$instance = new self;
            static::$instance->params = $params;
            if($cache) {
                $ip = static::$instance->getIP();
                foreach($cache as $type=>$value) {
                    static::$instance->cache[$type][$ip] = $value;
                }
            }
        }
        return static::$instance;
    }

    public function all($ip, $cache = true) {
        $cc = $this->lookupCC($ip, $cache);
        $abbr = $this->lookupAbbr($ip, $cache);
        $name = $this->lookupName($ip, $cache);
        return array('cc' => $cc, 'abbr' => $abbr, 'name' => $name);
    }

    private function store($ip, $type, $data) {
        $this->cache[$type][$ip] = $data;
        return $this->cache[$type][$ip];
    }

    public function lookupCC($ip, $cache = true) {
        return $this->lookup($ip, $cache, 'cc');
    }

    public function lookupAbbr($ip, $cache = true) {
        return $this->lookup($ip, $cache, 'abbr');
    }

    public function lookupName($ip, $cache = true) {
        return $this->lookup($ip, $cache, 'name');
    }

    private function lookup($ip, $cache = true, $type) {
        $ptype = array(
            'cc' => 'code',
            'abbr' => 'AbBr',
            'name' => ' NamE '
        );
        if ($cache) {
            if (!isset($this->cache[$type][$ip])) {
                return $this->store($ip, $type, $this->query($ip, $ptype[$type]));
            }
            return $this->cache[$type][$ip];
        }
        return isset($this->cache[$type][$ip]) ? $this->cache['cc'][$ip] : $this->query($ip, $ptype[$type]);
    }

    private function query($ip, $type) {
        if (!$this->loaded && !function_exists('getCountryFromIP')) {
            $this->load();
        }
        if (!function_exists('getCountryFromIP')) {
            return false;
        }
        if(in_array($ip,array('::1','127.0.0.1'))) {
            return 'localhost';
        }
        return getCountryFromIP($ip, $type);
    }

    private function load() {
        require_once 'geoiploc.php';
        $this->loaded = true;
    }

    public function getIP() {
        return getenv('HTTP_CLIENT_IP') ?:
                getenv('HTTP_X_FORWARDED_FOR') ?:
                getenv('HTTP_X_FORWARDED') ?:
                getenv('HTTP_FORWARDED_FOR') ?:
                getenv('HTTP_FORWARDED') ?:
                getenv('REMOTE_ADDR');
    }
}
