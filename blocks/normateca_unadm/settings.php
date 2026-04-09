<?php
/**
 * @package block_normateca_unadm
 * @copyright 2023, Luis Felipe Alcocer  <luisfelipealcocersosa@gmail.com>
 */

defined('MOODLE_INTERNAL') || die;



    $settings->add(new admin_setting_configtext('block_normateca_unadm/hostdb', get_string('hostdb', 'block_normateca_unadm'),
        get_string('hostdbinfo', 'block_normateca_unadm'), '', PARAM_RAW, 50));

    $settings->add(new admin_setting_configtext('block_normateca_unadm/userdb', get_string('userdb', 'block_normateca_unadm'),
        get_string('userdbinfo', 'block_normateca_unadm'), '', PARAM_RAW, 50));

    $settings->add(new admin_setting_configpasswordunmask('block_normateca_unadm/passdb', get_string('passdb', 'block_normateca_unadm'),
        get_string('passdbinfo', 'block_normateca_unadm'), ''));

    $settings->add(new admin_setting_configtext('block_normateca_unadm/dbname', get_string('dbname', 'block_normateca_unadm'),
        get_string('dbnameinfo', 'block_normateca_unadm'), '', PARAM_RAW, 50));

    $settings->add(new admin_setting_configtext('block_normateca_unadm/siteexternal', get_string('siteexternal', 'block_normateca_unadm'),
        get_string('siteexternalinfo', 'block_normateca_unadm'), 'https://100tecnicasdidacticas.unadmexico.mx/local/normateca/img/', PARAM_RAW, 50));

