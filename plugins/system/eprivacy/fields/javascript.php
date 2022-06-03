<?php

/**
 * @copyright	Copyright (C) 2010 Michael Richey. All rights reserved.
 * @license		GNU General Public License version 3; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
jimport('joomla.version');

class JFormFieldJavascript extends JFormField {

    protected $type = 'Javascript';

    protected function getLabel() {
        return '';
    }

    protected function getInput() {
        $debug = JFactory::getConfig()->get('debug',false)?'':'.min';
        JFactory::getDocument()->addScript(JURI::root(true) . '/media/plg_system_eprivacy/js/admin.class' . $debug . '.js');
        return;
    }

}
