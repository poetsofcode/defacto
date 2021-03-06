<?php
defined('_JEXEC') or die('Restricted access');
?><div class="acym__configuration__subscription acym__content acym_area padding-vertical-1 padding-horizontal-2">
	<div class="acym_area_title"><?php echo acym_translation('ACYM_SUBSCRIPTION'); ?></div>
	<div class="grid-x">
		<div class="cell grid-x grid-margin-x">
            <?php echo acym_switch('config[allow_visitor]', $this->config->get('allow_visitor'), acym_translation('ACYM_ALLOW_VISITOR'), [], 'xlarge-3 medium-5 small-9', "auto", "tiny"); ?>
		</div>
		<div class="cell grid-x grid-margin-x">
            <?php
            echo acym_switch(
                'config[generate_name]',
                $this->config->get('generate_name'),
                acym_translation('ACYM_GENERATE_NAME').acym_info('ACYM_GENERATE_NAME_DESC'),
                [],
                'xlarge-3 medium-5 small-9',
                'auto',
                'tiny'
            );
            ?>
		</div>
		<div class="cell grid-x grid-margin-x">
            <?php echo acym_switch('config[require_confirmation]', $this->config->get('require_confirmation'), acym_translation('ACYM_REQUIRE_CONFIRMATION'), [], 'xlarge-3 medium-5 small-9', "auto", "tiny", 'confirm_config'); ?>
		</div>
		<div class="cell grid-x" id="confirm_config">
			<div class="cell grid-x">
				<div class="cell xlarge-3 medium-5"></div>
				<div class="cell medium-auto">
					<a class=" button" href="<?php echo acym_completeLink('mails&task=edit&notification=acy_confirm&type_editor=acyEditor'); ?>"><?php echo acym_translation('ACYM_EDIT_EMAIL'); ?></a>
				</div>
			</div>
			<label for="confirm_redirect" class="cell grid-x margin-bottom-1">
				<span class="cell xlarge-3 medium-5 acym_vcenter"><?php echo acym_translation('ACYM_CONFIRMATION_REDIRECTION'); ?></span>
				<input id="confirm_redirect" class="cell xlarge-4 medium-auto margin-bottom-0" type="text" name="config[confirm_redirect]" value="<?php echo acym_escape($this->config->get('confirm_redirect')); ?>">
				<span class="cell large-auto hide-for-large-only hide-for-medium-only"></span>
			</label>
		</div>
		<div class="cell medium-3"><?php echo acym_translation('ACYM_ALLOW_MODIFICATION'); ?></div>
		<div class="cell medium-9">
            <?php
            $allowModif = [
                'none' => acym_translation('ACYM_NO'),
                'data' => acym_translation('ACYM_ALLOW_ONLY_THEIRS'),
                'all' => acym_translation('ACYM_YES'),
            ];
            echo acym_radio($allowModif, 'config[allow_modif]', $this->config->get('allow_modif', 'data'));
            ?>
		</div>
	</div>
</div>

<div class="acym__configuration__subscription acym__content acym_area padding-vertical-1 padding-horizontal-2">
	<div class="acym_area_title"><?php echo acym_translation_sprintf('ACYM_XX_INTEGRATION', ACYM_CMS_TITLE); ?></div>

    <?php
    if (!acym_isPluginActive('acymtriggers')) {
        acym_display(acym_translation_sprintf('ACYM_NEEDS_SYSTEM_PLUGIN', 'AcyMailing - Joomla integration'), 'error', false);
    }
    ?>

	<div class="grid-x">
		<div class="cell grid-x grid-margin-x">
            <?php
            echo acym_switch(
                'config[regacy]',
                $this->config->get('regacy'),
                acym_translation('ACYM_CREATE_ACY_USER_FOR_CMS_USER'),
                [],
                'xlarge-3 medium-5 small-9',
                'auto',
                'tiny',
                'acym__config__regacy'
            );
            ?>
		</div>
		<div class="cell grid-x" id="acym__config__regacy">
			<div class="cell grid-x grid-margin-x">
                <?php
                echo acym_switch(
                    'config[regacy_forceconf]',
                    $this->config->get('regacy_forceconf'),
                    acym_translation('ACYM_SEND_CONF_REGACY'),
                    [],
                    'xlarge-3 medium-5 small-9',
                    'auto',
                    'tiny',
                    'regforceconf_config'
                );
                ?>
			</div>
			<div class="cell grid-x grid-margin-x">
                <?php
                echo acym_switch(
                    'config[regacy_delete]',
                    $this->config->get('regacy_delete'),
                    acym_translation('ACYM_DELETE_USER_OF_CMS_USER'),
                    [],
                    'xlarge-3 medium-5 small-9',
                    'auto',
                    'tiny',
                    'regdelete_config'
                );
                ?>
			</div>

            <?php acym_trigger('onRegacyUseExternalPlugins', []); ?>

			<div class="cell xlarge-3 medium-5">
				<label for="acym__config__regacy-text" title="<?php echo acym_escape(acym_translation('ACYM_SUBSCRIBE_CAPTION_DESC')); ?>">
                    <?php echo acym_escape(acym_translation('ACYM_SUBSCRIBE_CAPTION')); ?>
				</label>
			</div>
			<div class="cell xlarge-4 medium-7">
				<input type="text" name="config[regacy_text]" id="acym__config__regacy-text" value="<?php echo acym_escape($this->config->get('regacy_text')); ?>" />
			</div>
			<div class="cell xlarge-5 hide-for-medium-only hide-for-small-only"></div>
			<div class="cell xlarge-3 medium-5">
				<label for="acym__config__regacy-lists">
                    <?php echo acym_tooltip(acym_translation('ACYM_DISPLAYED_LISTS'), acym_translation('ACYM_DISPLAYED_LISTS_DESC')); ?>
				</label>
			</div>
			<div class="cell xlarge-4 medium-7">
                <?php
                echo acym_selectMultiple(
                    $data['lists'],
                    'config[regacy_lists]',
                    explode(',', $this->config->get('regacy_lists')),
                    ['class' => 'acym__select', 'id' => 'acym__config__regacy-lists'],
                    'id',
                    'name'
                );
                ?>
			</div>
			<div class="cell xlarge-5 hide-for-medium-only hide-for-small-only"></div>

			<div class="cell xlarge-3 medium-5">
				<label for="acym__config__regacy-checkedlists">
                    <?php echo acym_tooltip(acym_translation('ACYM_CHECKED_LISTS'), acym_translation('ACYM_LISTS_CHECKED_DEFAULT_DESC')); ?>
				</label>
			</div>
			<div class="cell xlarge-4 medium-7">
                <?php
                echo acym_selectMultiple(
                    $data['lists'],
                    'config[regacy_checkedlists]',
                    explode(',', $this->config->get('regacy_checkedlists')),
                    ['class' => 'acym__select', 'id' => 'acym__config__regacy-checkedlists'],
                    'id',
                    'name'
                );
                ?>
			</div>
			<div class="cell xlarge-5 hide-for-medium-only hide-for-small-only"></div>

			<div class="cell xlarge-3 medium-5">
				<label for="acym__config__regacy-autolists">
                    <?php echo acym_tooltip(acym_translation('ACYM_AUTO_SUBSCRIBE_TO'), acym_translation('ACYM_AUTO_SUBSCRIBE_TO_DESC')); ?>
				</label>
			</div>
			<div class="cell xlarge-4 medium-7">
                <?php
                echo acym_selectMultiple(
                    $data['lists'],
                    'config[regacy_autolists]',
                    explode(',', $this->config->get('regacy_autolists')),
                    ['class' => 'acym__select', 'id' => 'acym__config__regacy-autolists'],
                    'id',
                    'name'
                );
                ?>
			</div>
			<div class="cell xlarge-5 hide-for-medium-only hide-for-small-only"></div>


            <?php
            if ('joomla' === ACYM_CMS) {
                $options = [
                    acym_selectOption('email', 'ACYM_EMAIL'),
                    acym_selectOption('password', 'ACYM_SMTP_PASSWORD'),
                    acym_selectOption('custom', 'ACYM_CUSTOM_FIELD'),
                ];
                ?>
				<div class="cell xlarge-3 medium-5">
					<label for="acym__config__regacy-listsposition">
                        <?php echo acym_escape(acym_translation('ACYM_LISTS_POSITION')); ?>
					</label>
				</div>
				<div class="cell xlarge-4 medium-7">
                    <?php
                    echo acym_select(
                        $options,
                        'config[regacy_listsposition]',
                        $this->config->get('regacy_listsposition', 'password'),
                        'class="acym__select" data-toggle-select="'.acym_escape('{"custom":"#acym__config__regacy__custom-list-position"}').'"',
                        'value',
                        'text',
                        'acym__config__regacy-listsposition'
                    );
                    ?>
				</div>
				<div class="cell xlarge-5 hide-for-medium-only hide-for-small-only"></div>

				<div class="cell grid-x" id="acym__config__regacy__custom-list-position">
					<div class="cell xlarge-3 medium-5"></div>
					<div class="cell xlarge-4 medium-7">
						<input type="text" name="config[regacy_listspositioncustom]" value="<?php echo acym_escape($this->config->get('regacy_listspositioncustom')); ?>" />
					</div>
				</div>
            <?php } ?>
		</div>
	</div>
</div>

<!-- Integrations -->
<?php
acym_trigger('onRegacyOptionsDisplay', [$data['lists']]);

