<?php
$min = JFactory::getConfig()->get('debug', false) ? '' : '.min';
JFactory::getDocument()->addStyleSheet(JURI::root(true) . '/media/plg_system_eprivacy/css/definitions' . $min . '.css', array('version' => 'auto'));
?>
<div class="cookietable">
    <table>
        <thead>
            <tr>
                <th><input type="checkbox" value="0" class="cookiesAll"></th>
                <th><?php echo JText::_('PLG_SYS_EPRIVACY_TH_COOKIENAME');?></th>
                <th><?php echo JText::_('PLG_SYS_EPRIVACY_TH_COOKIEDOMAIN');?></th>
                <th><?php echo JText::_('PLG_SYS_EPRIVACY_TH_COOKIEDESCRIPTION');?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$params->get('sessioncookie', 0)):?>
            <tr>
                <td><input type="checkbox" value="1" class="acl" checked="checked" disabled="disabled"/></td>
                <td><?php echo JText::_('PLG_SYS_EPRIVACY_TD_SESSIONCOOKIE');?></td>
                <td><?php echo JFactory::getConfig()->get('cookie_domain', '.' . filter_input(INPUT_SERVER, 'HTTP_HOST')); ?></td>
                <td><?php echo JText::_('PLG_SYS_EPRIVACY_TD_SESSIONCOOKIE_DESC');?></td>
            </tr>
            <?php endif; ?>
            <?php foreach((array)$params->get('cookies', array()) as $cookie):
                $name = preg_match('/PLG_SYS_EPRIVACY_COOKIENAME_/', $cookie->name) ? JText::_($cookie->name) : $cookie->name;
                $desc = preg_match('/PLG_SYS_EPRIVACY_COOKIEDESC_/', $cookie->desc) ? JText::_($cookie->desc) : $cookie->desc;
                $checked = $cookie->required ? ' checked="checked" disabled="disabled"' : '';
            ?>
            <tr>
                <td><input type="checkbox" value="<?php echo $cookie->acl;?>" class="acl"<?php echo $checked;?>/></td>
                <td><?php echo $name;?></td>
                <td><?php echo $cookie->domain;?></td>
                <td><?php echo $desc;?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>
