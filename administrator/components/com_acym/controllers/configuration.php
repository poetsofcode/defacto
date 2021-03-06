<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class ConfigurationController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_CONFIGURATION')] = acym_completeLink('configuration');
        $this->loadScripts = [
            'all' => ['introjs'],
        ];
    }

    public function listing()
    {
        acym_setVar('layout', 'listing');

        $data = [];
        $data['tab'] = acym_get('helper.tab');
        $this->prepareLanguages($data);
        $this->prepareLists($data);
        $this->prepareNotifications($data);
        $this->prepareAcl($data);

        parent::display($data);
    }

    private function prepareLanguages(&$data)
    {
        $langs = acym_getLanguages();
        $data['languages'] = [];

        foreach ($langs as $lang => $obj) {
            if (strlen($lang) != 5 || $lang == "xx-XX") continue;

            $oneLanguage = new stdClass();
            $oneLanguage->language = $lang;
            $oneLanguage->name = $obj->name;

            $linkEdit = acym_completeLink('language&task=displayLanguage&code='.$lang, true);
            $icon = $obj->exists ? 'edit' : 'add';
            $idModalLanguage = 'acym_modal_language_'.$lang;
            $oneLanguage->edit = acym_modal(
                '<i class="acymicon-'.$icon.' cursor-pointer acym__color__blue" data-open="'.$idModalLanguage.'" data-ajax="false" data-iframe="'.$linkEdit.'" data-iframe-class="acym__iframe_language" id="image'.$lang.'"></i>',
                '', //<iframe src="'.$linkEdit.'"></iframe>
                $idModalLanguage,
                'data-reveal-larger',
                '',
                false
            );

            $data['languages'][] = $oneLanguage;
        }
    }

    private function prepareLists(&$data)
    {
        $listClass = acym_get('class.list');
        $lists = $listClass->getAllWIthoutManagement();
        foreach ($lists as $i => $oneList) {
            if ($oneList->active == 0) {
                unset($lists[$i]);
            }
        }
        $data['lists'] = $lists;
    }

    private function prepareNotifications(&$data)
    {
        $data['notifications'] = [
            'acy_notification_create' => [
                'label' => 'ACYM_NOTIFICATION_CREATE',
                'tooltip' => '',
            ],
            'acy_notification_unsub' => [
                'label' => 'ACYM_NOTIFICATION_UNSUB',
                'tooltip' => '',
            ],
            'acy_notification_unsuball' => [
                'label' => 'ACYM_NOTIFICATION_UNSUBALL',
                'tooltip' => '',
            ],
            'acy_notification_subform' => [
                'label' => 'ACYM_NOTIFICATION_SUBFORM',
                'tooltip' => '',
            ],
            'acy_notification_profile' => [
                'label' => 'ACYM_NOTIFICATION_PROFILE',
                'tooltip' => '',
            ],
            'acy_notification_confirm' => [
                'label' => 'ACYM_NOTIFICATION_CONFIRM',
                'tooltip' => '',
            ],
        ];
    }

    private function prepareAcl(&$data)
    {
        $data['acl'] = acym_cmsPermission();
    }

    public function checkDB()
    {
        $messages = [];

        $queries = file_get_contents(ACYM_BACK.'tables.sql');
        $tables = explode('CREATE TABLE IF NOT EXISTS ', $queries);
        $structure = [];
        $createTable = [];
        $indexes = [];

        foreach ($tables as $oneTable) {
            if (strpos($oneTable, '`#__') !== 0) {
                continue;
            }

            $tableName = substr($oneTable, 1, strpos($oneTable, '`', 1) - 1);

            $fields = explode("\n", $oneTable);
            foreach ($fields as $oneField) {
                if (strpos($oneField, '#__') === 1) {
                    continue;
                }
                $oneField = rtrim(trim($oneField), ',');

                if (substr($oneField, 0, 1) == '`') {
                    $columnName = substr($oneField, 1, strpos($oneField, '`', 1) - 1);
                    $structure[$tableName][$columnName] = trim($oneField, ',');
                    continue;
                }

                if (strpos($oneField, 'PRIMARY KEY') === 0) {
                    $indexes[$tableName]['PRIMARY'] = $oneField;
                } elseif (strpos($oneField, 'INDEX') === 0) {
                    $firstBackquotePos = strpos($oneField, '`');
                    $indexName = substr($oneField, $firstBackquotePos + 1, strpos($oneField, '`', $firstBackquotePos + 1) - $firstBackquotePos - 1);

                    $indexes[$tableName][$indexName] = $oneField;
                }
            }
            $createTable[$tableName] = 'CREATE TABLE IF NOT EXISTS '.$oneTable;
        }


        $columnNames = [];
        $tableNames = array_keys($structure);

        foreach ($tableNames as $oneTableName) {
            try {
                $columns = acym_loadObjectList('SHOW COLUMNS FROM '.$oneTableName);
            } catch (Exception $e) {
                $columns = null;
            }

            if (!empty($columns)) {
                foreach ($columns as $oneField) {
                    $columnNames[$oneTableName][$oneField->Field] = $oneField->Field;
                }
                continue;
            }


            $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
            $messages[] = '<span style="color:blue">'.acym_translation_sprintf('ACYM_CHECKDB_LOAD_COLUMNS_ERROR', $oneTableName, $errorMessage).'</span>';

            if (strpos($errorMessage, 'marked as crashed')) {
                $repairQuery = 'REPAIR TABLE '.$oneTableName;

                try {
                    $isError = acym_query($repairQuery);
                } catch (Exception $e) {
                    $isError = null;
                }

                if ($isError === null) {
                    $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
                    $messages[] = '<span style="color:red">'.acym_translation_sprintf('ACYM_CHECKDB_REPAIR_TABLE_ERROR', $oneTableName, $errorMessage).'</span>';
                } else {
                    $messages[] = '<span style="color:green">'.acym_translation_sprintf('ACYM_CHECKDB_REPAIR_TABLE_SUCCESS', $oneTableName).'</span>';
                }
                continue;
            }

            try {
                $isError = acym_query($createTable[$oneTableName]);
            } catch (Exception $e) {
                $isError = null;
            }

            if ($isError === null) {
                $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
                $messages[] = '<span style="color:red">'.acym_translation_sprintf('ACYM_CHECKDB_CREATE_TABLE_ERROR', $oneTableName, $errorMessage).'</span>';
            } else {
                $messages[] = '<span style="color:green">'.acym_translation_sprintf('ACYM_CHECKDB_CREATE_TABLE_SUCCESS', $oneTableName).'</span>';
            }
        }

        foreach ($tableNames as $oneTableName) {
            if (empty($columnNames[$oneTableName])) continue;

            $idealColumnNames = array_keys($structure[$oneTableName]);
            $missingColumns = array_diff($idealColumnNames, $columnNames[$oneTableName]);

            if (!empty($missingColumns)) {
                foreach ($missingColumns as $oneColumn) {
                    $messages[] = '<span style="color:blue">'.acym_translation_sprintf('ACYM_CHECKDB_MISSING_COLUMN', $oneColumn, $oneTableName).'</span>';
                    try {
                        $isError = acym_query('ALTER TABLE '.$oneTableName.' ADD '.$structure[$oneTableName][$oneColumn]);
                    } catch (Exception $e) {
                        $isError = null;
                    }
                    if ($isError === null) {
                        $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
                        $messages[] = '<span style="color:red">'.acym_translation_sprintf('ACYM_CHECKDB_ADD_COLUMN_ERROR', $oneColumn, $oneTableName, $errorMessage).'</span>';
                    } else {
                        $messages[] = '<span style="color:green">'.acym_translation_sprintf('ACYM_CHECKDB_ADD_COLUMN_SUCCESS', $oneColumn, $oneTableName).'</span>';
                    }
                }
            }


            $results = acym_loadObjectList('SHOW INDEX FROM '.$oneTableName, 'Key_name');
            if (empty($results)) {
                $results = [];
            }

            foreach ($indexes[$oneTableName] as $name => $query) {
                $name = acym_prepareQuery($name);
                if (in_array($name, array_keys($results))) continue;


                $keyName = $name == 'PRIMARY' ? 'primary key' : 'index '.$name;

                $messages[] = '<span style="color:blue">'.acym_translation_sprintf('ACYM_CHECKDB_MISSING_INDEX', $keyName, $oneTableName).'</span>';
                try {
                    $isError = acym_query('ALTER TABLE '.$oneTableName.' ADD '.$query);
                } catch (Exception $e) {
                    $isError = null;
                }

                if ($isError === null) {
                    $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
                    $messages[] = '<span style="color:red">'.acym_translation_sprintf('ACYM_CHECKDB_ADD_INDEX_ERROR', $keyName, $oneTableName, $errorMessage).'</span>';
                } else {
                    $messages[] = '<span style="color:green">'.acym_translation_sprintf('ACYM_CHECKDB_ADD_INDEX_SUCCESS', $keyName, $oneTableName).'</span>';
                }
            }
        }

        $urlClass = acym_get('class.url');
        $duplicatedUrls = $urlClass->getDuplicatedUrls();

        if (!empty($duplicatedUrls)) {
            $time = time();
            $interrupted = false;
            $messages[] = '<span style="color:blue">'.acym_translation('ACYM_CHECKDB_DUPLICATED_URLS').'</span>';

            $maxexecutiontime = intval($this->config->get('max_execution_time'));
            if (empty($maxexecutiontime) || $maxexecutiontime - 20 < 20) {
                $maxexecutiontime = 20;
            } else {
                $maxexecutiontime -= 20;
            }

            acym_increasePerf();
            while (!empty($duplicatedUrls)) {
                $urlClass->delete($duplicatedUrls);

                if (time() - $time > $maxexecutiontime) {
                    $interrupted = true;
                    break;
                }

                $duplicatedUrls = $urlClass->getDuplicatedUrls();
            }
            if (empty($interrupted)) {
                $messages[] = '<span style="color:green">'.acym_translation('ACYM_CHECKDB_DUPLICATED_URLS_SUCCESS').'</span>';
            } else {
                $messages[] = '<span style="color:blue">'.acym_translation('ACYM_CHECKDB_DUPLICATED_URLS_REMAINING').'</span>';
            }
        }

        if (empty($messages)) {
            echo '<i class="acymicon-check-circle acym__color__green"></i>';
        } else {
            echo implode('<br />', $messages);
        }

        exit;
    }

    public function store()
    {
        acym_checkToken();

        $formData = acym_getVar('array', 'config', []);
        if (empty($formData)) return false;

        if ($formData['from_as_replyto'] == 1) {
            $formData['replyto_name'] = $formData['from_name'];
            $formData['replyto_email'] = $formData['from_email'];
        }

        $select2Fields = [
            'regacy_lists',
            'regacy_checkedlists',
            'regacy_autolists',
            'acy_notification_create',
            'acy_notification_unsub',
            'acy_notification_unsuball',
            'acy_notification_subform',
            'acy_notification_profile',
            'acy_notification_confirm',
            'wp_access',
        ];
        foreach ($select2Fields as $oneField) {
            $formData[$oneField] = !empty($formData[$oneField]) ? $formData[$oneField] : [];
        }
        acym_trigger('onBeforeSaveConfigFields', [&$formData]);

        $status = $this->config->save($formData);

        if ($status) {
            acym_enqueueMessage(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success');
        } else {
            acym_enqueueMessage(acym_translation('ACYM_ERROR_SAVING'), 'error');
        }

        $this->config->load();

        return true;
    }

    public function test()
    {
        $this->store();

        $mailerHelper = acym_get('helper.mailer');
        $addedName = $this->config->get('add_names', true) ? $mailerHelper->cleanText(acym_currentUserName()) : '';

        $mailerHelper->AddAddress(acym_currentUserEmail(), $addedName);
        $mailerHelper->Subject = 'Test e-mail from '.ACYM_LIVE;
        $mailerHelper->Body = acym_translation('ACYM_TEST_EMAIL');
        $mailerHelper->SMTPDebug = 1;
        if (acym_isDebug()) {
            $mailerHelper->SMTPDebug = 2;
        }

        $mailerHelper->isHTML(false);
        $result = $mailerHelper->send();

        if (!$result) {
            $sendingMethod = $this->config->get('mailer_method');

            if ($sendingMethod == 'smtp') {
                if ($this->config->get('smtp_secured') == 'ssl' && !function_exists('openssl_sign')) {
                    acym_enqueueMessage(acym_translation('ACYM_OPENSSL'), 'notice');
                }

                if (!$this->config->get('smtp_auth') && strlen($this->config->get('smtp_password')) > 1) {
                    acym_enqueueMessage(acym_translation('ACYM_ADVICE_SMTP_AUTH'), 'notice');
                }

                if ($this->config->get('smtp_port') && !in_array($this->config->get('smtp_port'), [25, 2525, 465, 587])) {
                    acym_enqueueMessage(acym_translation_sprintf('ACYM_ADVICE_PORT', $this->config->get('smtp_port')), 'notice');
                }
            }

            if (acym_isLocalWebsite() && in_array($sendingMethod, ['sendmail', 'qmail', 'mail'])) {
                acym_enqueueMessage(acym_translation('ACYM_ADVICE_LOCALHOST'), 'notice');
            }

            $bounce = $this->config->get('bounce_email');
            if (!empty($bounce) && !in_array($sendingMethod, ['smtp', 'elasticemail'])) {
                acym_enqueueMessage(acym_translation_sprintf('ACYM_ADVICE_BOUNCE', '<b>'.$bounce.'</b>'), 'notice');
            }
        }

        $this->listing();
    }

    public function ports()
    {
        if (!function_exists('fsockopen')) {
            echo '<span style="color:red">'.acym_translation('ACYM_FSOCKOPEN').'</span>';
            exit;
        }

        $tests = [25 => 'smtp.sendgrid.com', 2525 => 'smtp.sendgrid.com', 587 => 'smtp.sendgrid.com', 465 => 'ssl://smtp.sendgrid.com'];
        $total = 0;
        foreach ($tests as $port => $server) {
            $fp = @fsockopen($server, $port, $errno, $errstr, 5);
            if ($fp) {
                echo '<br /><span style="color:#3dea91" >'.acym_translation_sprintf('ACYM_SMTP_AVAILABLE_PORT', $port).'</span>';
                fclose($fp);
                $total++;
            } else {
                echo '<br /><span style="color:#ff5259" >'.acym_translation_sprintf('ACYM_SMTP_NOT_AVAILABLE_PORT', $port, $errno.' - '.utf8_encode($errstr)).'</span>';
            }
        }

        exit;
    }

    public function detecttimeout()
    {
        acym_query('REPLACE INTO `#__acym_configuration` (`name`,`value`) VALUES ("max_execution_time","5"), ("last_maxexec_check","'.time().'")');
        @ini_set('max_execution_time', 600);
        @ignore_user_abort(true);
        $i = 0;
        while ($i < 480) {
            sleep(8);
            $i += 10;
            acym_query('UPDATE `#__acym_configuration` SET `value` = "'.intval($i).'" WHERE `name` = "max_execution_time"');
            acym_query('UPDATE `#__acym_configuration` SET `value` = "'.intval(time()).'" WHERE `name` = "last_maxexec_check"');
            sleep(2);
        }
        exit;
    }

    public function deletereport()
    {
        $path = trim(html_entity_decode($this->config->get('cron_savepath')));
        if (!preg_match('#^[a-z0-9/_\-{}]*\.log$#i', $path)) {
            acym_enqueueMessage(acym_translation('ACYM_WRONG_LOG_NAME'), 'error');

            return;
        }

        $path = str_replace(['{year}', '{month}'], [date('Y'), date('m')], $this->config->get('cron_savepath'));
        $reportPath = acym_cleanPath(ACYM_ROOT.$path);

        if (is_file($reportPath)) {
            $result = acym_deleteFile($reportPath);
            if ($result) {
                acym_enqueueMessage(acym_translation('ACYM_SUCC_DELETE_LOG'), 'success');
            } else {
                acym_enqueueMessage(acym_translation('ACYM_ERROR_DELETE_LOG'), 'error');
            }
        } else {
            acym_enqueueMessage(acym_translation('ACYM_EXIST_LOG'), 'info');
        }

        return $this->listing();
    }

    public function seereport()
    {
        acym_noCache();

        $path = trim(html_entity_decode($this->config->get('cron_savepath')));
        if (!preg_match('#^[a-z0-9/_\-{}]*\.log$#i', $path)) {
            acym_display(acym_translation('ACYM_WRONG_LOG_NAME'), 'error');
        }

        $path = str_replace(['{year}', '{month}'], [date('Y'), date('m')], $path);
        $reportPath = acym_cleanPath(ACYM_ROOT.$path);

        if (file_exists($reportPath) && !is_dir($reportPath)) {
            try {
                $lines = 5000;
                $f = fopen($reportPath, "rb");
                fseek($f, -1, SEEK_END);
                if (fread($f, 1) != "\n") {
                    $lines -= 1;
                }

                $report = '';
                while (ftell($f) > 0 && $lines >= 0) {
                    $seek = min(ftell($f), 4096); // Figure out how far back we should jump
                    fseek($f, -$seek, SEEK_CUR);
                    $report = ($chunk = fread($f, $seek)).$report; // Get the line
                    fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
                    $lines -= substr_count($chunk, "\n"); // Move to previous line
                }

                while ($lines++ < 0) {
                    $report = substr($report, strpos($report, "\n") + 1);
                }
                fclose($f);
            } catch (Exception $e) {
                $report = '';
            }
        }

        if (empty($report)) {
            $report = acym_translation('ACYM_EMPTY_LOG');
        }

        echo nl2br($report);
        exit;
    }

    public function redomigration()
    {
        $newConfig = new stdClass();
        $newConfig->migration = 0;
        $this->config->save($newConfig);

        acym_redirect(acym_completeLink('dashboard', false, true));
    }

    public function removeNotification()
    {
        $whichNotification = acym_getVar('string', 'id');

        if ($whichNotification != 0 && empty($whichNotification)) {
            echo json_encode(['error' => acym_translation('ACYM_NOTIFICATION_NOT_FOUND')]);
            exit;
        }

        if ('all' === $whichNotification) {
            $this->config->save(['notifications' => '[]']);
            $notifications = [];
        } else {
            $notifications = json_decode($this->config->get('notifications', '[]'), true);
            unset($notifications[$whichNotification]);
            $this->config->save(['notifications' => json_encode($notifications)]);
        }
        $helperHeader = acym_get('helper.header');

        echo json_encode(['data' => $helperHeader->getNotificationCenterInner($notifications)]);
        exit;
    }

    public function markNotificationRead()
    {
        $which = acym_getVar('string', 'id');

        $notifications = json_decode($this->config->get('notifications', '[]'), true);
        if (empty($notifications)) {
            echo json_encode(['message' => 'done']);
            exit;
        }

        if (empty($which)) {
            foreach ($notifications as $key => $notification) {
                $notifications[$key]['read'] = true;
            }
        } else {
            foreach ($notifications as $key => $notification) {
                if ($notification['id'] != $which) continue;
                $notifications[$key]['read'] = true;
            }
        }


        $this->config->save(['notifications' => json_encode($notifications)]);

        echo json_encode(['message' => 'done']);
        exit;
    }

    public function addNotification()
    {
        $message = acym_getVar('string', 'message');
        $level = acym_getVar('string', 'level');

        if (empty($message) || empty($level)) {
            echo json_encode(['error' => acym_translation('ACYM_INFORMATION_MISSING')]);
            exit;
        }

        $helperHeader = acym_get('helper.header');

        $newNotification = new stdClass();
        $newNotification->message = $message;
        $newNotification->level = $level;
        $newNotification->read = false;
        $newNotification->date = time();

        $helperHeader->addNotification($newNotification);

        echo json_encode(['data' => $helperHeader->getNotificationCenter()]);
        exit;
    }

    public function getAjax()
    {
        acym_checkToken();

        $field = acym_getVar('string', 'field', '');
        $res = $this->config->get($field, '');

        if (empty($res)) {
            echo json_encode(['error' => acym_translation('ACYM_COULD_NOT_LOAD_INFORMATION')]);
        } else {
            echo json_encode(['data' => $res]);
        }

        exit;
    }
}

