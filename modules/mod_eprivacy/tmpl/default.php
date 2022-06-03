<?php
/**
 * @subpackage	mod_eprivacy
 * @copyright	Copyright (C) 2005 - 2012 Michael Richey. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$target = $pluginparams->get('policytarget', '_blank');
?>
<div class="plg_system_eprivacy_module">
    <?php if($pluginparams->get('displaytype','message') != "message") : ?>
    <div class="plg_system_eprivacy_message" style="display:none">
        <?php if($params->get('showtitle',1)): ?>
            <h2><?php echo JText::_('PLG_SYS_EPRIVACY_MESSAGE_TITLE');?></h2>
        <?php endif; ?>
        <p><?php echo JText::_('PLG_SYS_EPRIVACY_MESSAGE'); ?></p>
        <?php if($policyurl) : ?>
            <p><a href="<?php echo $policyurl;?>" target="<?php echo $target;?>"><?php echo JText::_('PLG_SYS_EPRIVACY_POLICYTEXT');?></a></p>
        <?php endif; ?>
        <?php if($legallinks) : ?>
            <p><a href="<?php echo $legallinks[0];?>" onclick="window.open(this.href);return false;" target="<?php echo $target;?>"><?php echo JText::_('PLG_SYS_EPRIVACY_LAWLINK_TEXT'); ?></a></p>
            <p><a href="<?php echo $legallinks[1];?>" onclick="window.open(this.href);return false;" target="<?php echo $target;?>"><?php echo JText::_('PLG_SYS_EPRIVACY_GDPRLINK_TEXT'); ?></a></p>
        <?php endif; ?>
        <?php echo ePrivacyHelper::cookieTable($pluginparams); ?>
        <button class="plg_system_eprivacy_agreed"><?php echo JText::_('PLG_SYS_EPRIVACY_AGREE');?></button>
        <button class="plg_system_eprivacy_declined"><?php echo JText::_('PLG_SYS_EPRIVACY_DECLINE');?></button>
    </div>
    <div class="plg_system_eprivacy_declined" style="display:none">
        <p>
            <button class="plg_system_eprivacy_reconsider"><?php echo JText::_('PLG_SYS_EPRIVACY_RECONSIDER');?></button> 
            <?php echo JText::_('PLG_SYS_EPRIVACY_DECLINED'); ?>
        </p>
    </div>
    <?php endif; ?>
    <div class="plg_system_eprivacy_accepted" style="display:none">
        <p>
            <button class="plg_system_eprivacy_reconsider"><?php echo JText::_('PLG_SYS_EPRIVACY_PREFS');?></button> 
            <button class="plg_system_eprivacy_accepted"><?php echo JText::_('PLG_SYS_EPRIVACY_UNACCEPT');?></button> 
            <?php echo JText::_('PLG_SYS_EPRIVACY_UNACCEPT_MESSAGE'); ?>
        </p>
    </div>
</div>
<div id="plg_system_eprivacy" style="display:none"></div>