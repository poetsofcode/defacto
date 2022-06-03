<div class="activebar-container" style="display:none">
    <p><?php echo JText::_('PLG_SYS_EPRIVACY_MESSAGE'); ?></p>
    <ul class="links">
        <?php if (strlen(trim($this->_options['policyurl']))) : ?>
            <li><a href="<?php echo $this->_options['policyurl']; ?>" target="<?php echo $this->params->get('policytarget','_blank');?>"><?php echo JText::_('PLG_SYS_EPRIVACY_POLICYTEXT'); ?></a></li>
        <?php endif; ?>
        <?php if (count($this->_options['lawlink'])) : ?>
            <li>
                <a href="<?php echo $this->_options['lawlink'][0]; ?>" target="<?php echo $this->params->get('policytarget','_blank');?>">
                    <?php echo JText::_('PLG_SYS_EPRIVACY_LAWLINK_TEXT'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $this->_options['lawlink'][1]; ?>" target="<?php echo $this->params->get('policytarget','_blank');?>">
                    <?php echo JText::_('PLG_SYS_EPRIVACY_GDPRLINK_TEXT'); ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <?php echo ePrivacyHelper::cookieTable($this->params); ?>
    <button class="decline <?php echo $this->_options['declineclass']; ?>"><?php echo JText::_('PLG_SYS_EPRIVACY_DECLINE'); ?></button>
    <button class="accept <?php echo $this->_options['agreeclass']; ?>"><?php echo JText::_('PLG_SYS_EPRIVACY_AGREE'); ?></button>
</div>