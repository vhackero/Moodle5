<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig || has_capability('local/segmentmenu:manage', context_system::instance())) {
    $settings = new admin_settingpage(
        'local_segmentmenu_settings',
        get_string('settings', 'local_segmentmenu'),
        'local/segmentmenu:manage'
    );

    $settings->add(new admin_setting_configtext(
        'local_segmentmenu/segmentfield',
        get_string('segmentfield', 'local_segmentmenu'),
        get_string('segmentfield_desc', 'local_segmentmenu'),
        'segmento',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_configselect(
        'local_segmentmenu/menuposition',
        get_string('menuposition', 'local_segmentmenu'),
        get_string('menuposition_desc', 'local_segmentmenu'),
        'right',
        [
            'right' => get_string('positionright', 'local_segmentmenu'),
            'left' => get_string('positionleft', 'local_segmentmenu'),
            'sticky' => get_string('positionsticky', 'local_segmentmenu'),
        ]
    ));

    $settings->add(new admin_setting_configtextarea(
        'local_segmentmenu/menuitems',
        get_string('menuitems', 'local_segmentmenu'),
        get_string('menuitems_desc', 'local_segmentmenu'),
        '',
        PARAM_RAW,
        '60',
        '10'
    ));

    $ADMIN->add('localplugins', $settings);
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_segmentmenu_manage',
        get_string('manage', 'local_segmentmenu'),
        new moodle_url('/local/segmentmenu/index.php'),
        'local/segmentmenu:manage'
    ));
}
