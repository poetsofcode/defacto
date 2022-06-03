<?php
$policyurl = ePrivacyHelper::getPolicyURL($this->params);
$agreebutton = '<button class="plg_system_eprivacy_agreed btn btn-success">' . JText::_('PLG_SYS_EPRIVACY_AGREE') . '</button>';
$declinebutton = '<button class="plg_system_eprivacy_declined btn btn-danger">' . JText::_('PLG_SYS_EPRIVACY_DECLINE') . '</button>';
$modaloptions = array(
    'title' => JText::_('PLG_SYS_EPRIVACY_MESSAGE_TITLE'),
    'backdrop' => 'static',
    'keyboard' => false,
    'closeButton' => false,
    'footer' => $agreebutton . $declinebutton
);
$modalbody = '<p>' . JText::_('PLG_SYS_EPRIVACY_MESSAGE') . '</p>';
$modallinks = array();
if (strlen($policyurl)) {
    $modallinks[] = '<a href="' . $policyurl . '" target="' . $this->params->get('policytarget', '_blank') . '">' . JText::_('PLG_SYS_EPRIVACY_POLICYTEXT') . '</a>';
}
if ($this->params->get('lawlink', 1)) {
    $links = ePrivacyHelper::legalLinks();
    $modallinks[] = '<a href="' . $links[0] . '" target="_BLANK">' . JText::_('PLG_SYS_EPRIVACY_LAWLINK_TEXT') . '</a>';
    $modallinks[] = '<a href="' . $links[1] . '" target="_BLANK">' . JText::_('PLG_SYS_EPRIVACY_GDPRLINK_TEXT') . '</a>';
}
if (count($modallinks)) {
    $modalbody .= '<ul><li>' . implode('</li><li>', $modallinks) . '</li></ul>';
}
echo JHtml::_('bootstrap.renderModal', 'eprivacyModal', $modaloptions, $modalbody . ePrivacyHelper::cookieTable($this->params));