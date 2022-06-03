<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

$data = $displayData;
$offcanvs_position = $displayData->params->get('offcanvas_position', 'right');

$feature_folder_path     = JPATH_THEMES . '/' . $data->template->template . '/features/';

include_once $feature_folder_path.'logo.php';
include_once $feature_folder_path.'social.php';
include_once $feature_folder_path.'contact.php';
include_once $feature_folder_path.'menu.php';

$output  = '';

$output .= '<div id="sp-top-bar">';
$output .= '<div class="container">';
$output .= '<div class="container-inner">';
$output .= '<div class="row">';

$output .= '<div id="sp-top1" class="col-lg-7 col-md-8 hidden-xs">';
$output .= '<div class="sp-column text-right text-lg-right">';
$social = new HelixUltimateFeatureSocial($data->params);
if(isset($social->load_pos) && $social->load_pos == 'before')
{
    $output .= $social->renderFeature();
    $output .= '<jdoc:include type="modules" name="top1" style="sp_xhtml" />';
}
else
{
    $output .= '<jdoc:include type="modules" name="top1" style="sp_xhtml" />';
    $output .= $social->renderFeature();
}
$output .= '</div>';
$output .= '</div>';

$output .= '<div id="sp-top2 sp-logo" class="col-lg-5 col-md-4">';
$output .= '<div class="sp-column text-left text-lg-left">';

$logo    = new HelixUltimateFeatureLogo($data->params);
if(isset($logo->load_pos) && $logo->load_pos == 'before')
{
    $output .= $logo->renderFeature();
    $output .= '<jdoc:include type="modules" name="logo" style="sp_xhtml" />';
}
else
{
    $output .= '<jdoc:include type="modules" name="logo" style="sp_xhtml" />';
    $output .= $logo->renderFeature();
}

$output .= '</div>';
$output .= '</div>';

$output .= '</div>';
$output .= '</div>';
$output .= '</div>';
$output .= '</div>';


$output .= '<header id="sp-header">';
$output .= '<div class="container">';
$output .= '<div class="container-inner">';
$output .= '<div class="row">';

$class1 = 'col-7 col-lg-0';
$class2 = 'col-5 col-lg-12';
if($offcanvs_position == 'left')
{
    $class1 = 'col-12 col-lg-3';
    $class2 = 'd-none d-lg-block col-lg-9';
}

$output .= '<div id="" class="'. $class1 .'">';
$output .= '<div class="sp-column">';

$output .= '</div>';
$output .= '</div>';

$output .= '<div id="sp-menu" class="'. $class2 .'">';
$output .= '<div class="sp-column">';
$menu    = new HelixUltimateFeatureMenu($data->params);
if(isset($menu->load_pos) && $menu->load_pos == 'before')
{
    $output .= $menu->renderFeature();
    $output .= '<jdoc:include type="modules" name="menu" style="sp_xhtml" />';
}
else
{
    $output .= '<jdoc:include type="modules" name="menu" style="sp_xhtml" />';
    $output .= $menu->renderFeature();
}
$output .= '</div>';
$output .= '</div>';

$output .= '</div>';
$output .= '</div>';
$output .= '</div>';
$output .= '</header>';

echo $output;
