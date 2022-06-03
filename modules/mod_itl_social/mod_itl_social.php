<?php
/**
 * ITL Social by iThemesLab
 * 
 * @package    itl_social
 * @subpackage Modules
 * @license    GNU/GPL, see LICENSE.php
 * @link       http://ithemeslab.com
 */
 
// No direct access
defined('_JEXEC') or die;

//Get data from module paramater
$facebook 		= $params->get('facebook_url');
$twitter 		= $params->get('twitter_url');
$gplus 			= $params->get('gplus_url');
$youtube 		= $params->get('youtube_url');
$pinterest 		= $params->get('pinterest_url');
$dribbble 		= $params->get('dribbble_url');
$behance 		= $params->get('behance_url');
$instagram 		= $params->get('instagram_url');
$flickr 		= $params->get('flickr_url');
$git 	 		= $params->get('git_url');
$linkedin 		= $params->get('linkedin_url');
$rss 			= $params->get('rss_url');

require JModuleHelper::getLayoutPath('mod_itl_social', $params->get('layout'));