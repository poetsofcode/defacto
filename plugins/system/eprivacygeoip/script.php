<?php

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script file of AdminExile Pro
 */
class plgSystemEprivacygeoipInstallerScript {

    /**
     * Method to install the extension
     * $parent is the class calling this method
     *
     * @return void
     */
    function install($parent) {
        echo '<p>EU e-Privacy Directive GeoIP CLI Installed</p>';
    }

    /**
     * Method to uninstall the extension
     * $parent is the class calling this method
     *
     * @return void
     */
    function uninstall($parent) {
        echo '<p>EU e-Privacy Directive GeoIP has been uninstalled</p>';
    }

    /**
     * Method to update the extension
     * $parent is the class calling this method
     *
     * @return void
     */
//    function update($parent) {
//    }

    /**
     * Method to run before an install/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     *
     * @return void
     */
//	function preflight($type, $parent) 
//	{
//		echo '<p>Anything here happens before the installation/update/uninstallation of the module</p>';
//	}

    /**
     * Method to run after an install/update/uninstall method
     * $parent is the class calling this method
     * $type is the type of change (install, update or discover_install)
     *
     * @return void
     */
    function postflight($type, $parent) {
        // We only need to perform this if the extension is being installed, not updated
        if (strtolower($type) === 'install') {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $fields = array(
                $db->quoteName('enabled') . ' = 1'
            );

            $conditions = array(
                $db->quoteName('element') . ' = ' . $db->quote('eprivacygeoip'),
                $db->quoteName('type') . ' = ' . $db->quote('plugin')
            );

            $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);

            $db->setQuery($query);
            $db->execute();
        }
    }

}
