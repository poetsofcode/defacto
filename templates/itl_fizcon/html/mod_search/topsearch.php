<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_search
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

?>

<div class="top-search-wrapper">
    <div class="icon-top-wrapper">
        <i class="fa fa-search search-open-icon" aria-hidden="true"></i>
        <i class="fa fa-times search-close-icon" aria-hidden="true"></i>
    </div>
</div> <!-- /.top-search-wrapper -->
<div class="top-search-input-wrap">
    <div class="top-search-overlay"></div>
    
    <form action="<?php echo JRoute::_('index.php'); ?>" method="post">
        <div class="search-wrap">
            <div class="search <?php echo $moduleclass_sfx ?>">
                <?php
                $output = '<div class="sp_search_input"><input name="searchword" maxlength="' . $maxlength . '"  class="mod-search-searchword inputbox' . $moduleclass_sfx . '" type="text" size="' . $width . '" value="' . $text . '"  onblur="if (this.value==\'\') this.value=\'' . $text . '\';" onfocus="if (this.value==\'' . $text . '\') this.value=\'\';" /></div>';

                if ($button) :
                    if ($imagebutton) :
                        $button = '<input type="image" value="' . $button_text . '" class="button' . $moduleclass_sfx . '" src="' . $img . '" onclick="this.form.searchword.focus();"/>';
                    else :
                        $button = '<input type="submit" value="' . $button_text . '" class="button' . $moduleclass_sfx . '" onclick="this.form.searchword.focus();"/>';
                    endif;
                endif;

                switch ($button_pos) :
                    case 'top' :
                        $button = $button . '<br />';
                        $output = $button;
                        break;

                    case 'bottom' :
                        $button = '<br />' . $button;
                        $output = $output;
                        break;

                    case 'right' :
                        $output = $output;
                        break;

                    case 'left' :
                    default :
                        $output = $button;
                        break;
                endswitch;

                echo $output;
                ?>
                <input type="hidden" name="task" value="search" />
                <input type="hidden" name="option" value="com_search" />
                <input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
            </div>
        </div>
    </form>
</div> <!-- /.top-search-input-wrap -->