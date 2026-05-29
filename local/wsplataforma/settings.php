<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_wsplataforma', get_string('pluginname', 'local_wsplataforma'));

    $settings->add(new admin_setting_configcheckbox(
        'local_wsplataforma/unencryptedpassword',
        get_string('settings:unencryptedpassword', 'local_wsplataforma'),
        get_string('settings:unencryptedpassword_desc', 'local_wsplataforma'),
        0
    ));

    $settings->add(new admin_setting_configtext(
        'local_wsplataforma/sigiedbhost',
        get_string('settings:sigiedbhost', 'local_wsplataforma'),
        get_string('settings:sigiedbhost_desc', 'local_wsplataforma'),
        '',
        PARAM_HOST
    ));

    $settings->add(new admin_setting_configtext(
        'local_wsplataforma/sigiedbname',
        get_string('settings:sigiedbname', 'local_wsplataforma'),
        get_string('settings:sigiedbname_desc', 'local_wsplataforma'),
        '',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_wsplataforma/sigiedbuser',
        get_string('settings:sigiedbuser', 'local_wsplataforma'),
        get_string('settings:sigiedbuser_desc', 'local_wsplataforma'),
        '',
        PARAM_RAW_TRIMMED
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_wsplataforma/sigiedbpass',
        get_string('settings:sigiedbpass', 'local_wsplataforma'),
        get_string('settings:sigiedbpass_desc', 'local_wsplataforma'),
        ''
    ));

    $ADMIN->add('localplugins', $settings);
}
