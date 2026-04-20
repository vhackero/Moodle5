<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die;
if ( $hassiteconfig ) {
    global $DB;

    // Create the new settings page
    // - in a local plugin this is not defined as standard, so normal $settings->methods will throw an error as
    // $settings will be NULL
    $settings = new admin_settingpage('local_qrcurp', 'Configuración de QRCURP');

    // Create
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        $systemcontext = context_system::instance();
        $roles = role_fix_names(get_all_roles($systemcontext), $systemcontext, ROLENAME_ORIGINAL);
        $roleoptions = [];
        foreach ($roles as $role) {
            $roleoptions[$role->id] = $role->localname;
        }
        $defaultstudentroleid = (int) $DB->get_field('role', 'id', ['shortname' => 'student']);
        $defaultprofilefieldlist = implode(',', array_keys(\local_qrcurp\local\profile_fields_manager::default_fields()));

        $settings->add(new admin_setting_configcheckbox('local_qrcurp/validateprofilefields', get_string('validateprofilefields', 'local_qrcurp'),
            get_string('validateprofilefieldsinfo', 'local_qrcurp'), 1));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/autoinstallprofilefields', get_string('autoinstallprofilefields', 'local_qrcurp'),
            get_string('autoinstallprofilefieldsinfo', 'local_qrcurp'), 0));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/profilefieldslist', get_string('profilefieldslist', 'local_qrcurp'),
            get_string('profilefieldslistinfo', 'local_qrcurp'), $defaultprofilefieldlist, PARAM_RAW, 500));

        $missingprofilefields = [];
        if ((int) get_config('local_qrcurp', 'validateprofilefields') === 1) {
            $configuredfields = \local_qrcurp\local\profile_fields_manager::get_configured_shortnames();
            if ((int) get_config('local_qrcurp', 'autoinstallprofilefields') === 1) {
                \local_qrcurp\local\profile_fields_manager::ensure_fields($configuredfields);
            }
            $missingprofilefields = \local_qrcurp\local\profile_fields_manager::get_missing_fields($configuredfields);
        }
        $installerurl = new moodle_url('/local/qrcurp/installUserFields.php');
        if (empty($missingprofilefields)) {
            $profilefieldsstatus = get_string('profilefieldsok', 'local_qrcurp');
        } else {
            $profilefieldsstatus = get_string('profilefieldsmissing', 'local_qrcurp', implode(', ', $missingprofilefields));
            $profilefieldsstatus .= '<br><a href="'.$installerurl.'">'.get_string('profilefieldsinstalllink', 'local_qrcurp').'</a>';
        }
        $settings->add(new admin_setting_heading('local_qrcurp/profilefieldsstatus', get_string('profilefieldsstatus', 'local_qrcurp'), $profilefieldsstatus));

        $settings->add(new admin_setting_configtext('local_qrcurp/dbhost', get_string('dbhost', 'local_qrcurp'),
            get_string('dbhostinfo', 'local_qrcurp'), '', PARAM_HOST, 100));
        $settings->add(new admin_setting_configtext('local_qrcurp/dbport', get_string('dbport', 'local_qrcurp'),
            get_string('dbportinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configtext('local_qrcurp/dbname', get_string('dbname', 'local_qrcurp'),
            get_string('dbnameinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configtext('local_qrcurp/dbtable', get_string('dbtable', 'local_qrcurp'),
            get_string('dbtableinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configtext('local_qrcurp/dbuser', get_string('dbuser', 'local_qrcurp'),
            get_string('dbuserinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configpasswordunmask('local_qrcurp/dbpass', get_string('dbpass', 'local_qrcurp'),
            get_string('dbpassinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/dbinsert', get_string('dbinsert', 'local_qrcurp'),
            get_string('dbinsertinfo', 'local_qrcurp'), 0));
        $settings->add(new admin_setting_configtext('local_qrcurp/dateregistro', get_string('dateregistro', 'local_qrcurp'),
            get_string('dateregistroinfo', 'local_qrcurp'), '', PARAM_TEXT, 30));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/textregistro', get_string('textregistro', 'local_qrcurp'),
            get_string('textregistroinfo', 'local_qrcurp'), '', PARAM_RAW, 500));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/dateperiodos', get_string('dateperiodos', 'local_qrcurp'),
            get_string('dateperiodosinfo', 'local_qrcurp'), '', PARAM_RAW, 500));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/dateduracionperidos', get_string('dateduracionperidos', 'local_qrcurp'),
            get_string('dateduracionperidosinfo', 'local_qrcurp'), '', PARAM_RAW, 500));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/publicogeneral', get_string('publicogeneral', 'local_qrcurp'),
            get_string('publicogeneralinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/onlypublicogeneral', get_string('onlypublicogeneral', 'local_qrcurp'),
            get_string('onlypublicogeneralinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/idcategories', get_string('idcategories', 'local_qrcurp'),
            get_string('idcategoriesinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configtext('local_qrcurp/rolteacher', get_string('rolteacher', 'local_qrcurp'),
            get_string('rolteacherinfo', 'local_qrcurp'), '', PARAM_ALPHANUM, 3));
        $settings->add(new admin_setting_configtext('local_qrcurp/limitegroup', get_string('limitegroup', 'local_qrcurp'),
            get_string('limitegroupinfo', 'local_qrcurp'), '', PARAM_ALPHANUM, 5));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/studentxcategory', get_string('studentxcategory', 'local_qrcurp'),
            get_string('studentxcategoryinfo', 'local_qrcurp'), '', PARAM_RAW, 120));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/studentxcategorytext', get_string('studentxcategorytext', 'local_qrcurp'),
            get_string('studentxcategorytextinfo', 'local_qrcurp'), '', PARAM_RAW));
        $settings->add(new admin_setting_configselect('local_qrcurp/rolstudent', get_string('rolstudent', 'local_qrcurp'),
            get_string('rolstudentinfo', 'local_qrcurp'), $defaultstudentroleid, $roleoptions));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/haygroupespera', get_string('haygroupespera', 'local_qrcurp'),
            get_string('haygroupesperainfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configtext('local_qrcurp/namegroupespera', get_string('namegroupespera', 'local_qrcurp'),
            get_string('namegroupesperainfo', 'local_qrcurp'), '', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_qrcurp/defaultnamecategory', get_string('defaultnamecategory', 'local_qrcurp'),
            get_string('defaultnamecategoryinfo', 'local_qrcurp'),'', PARAM_RAW, 50));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/confirmemail', get_string('confirmemail', 'local_qrcurp'),
            get_string('confirmemailinfo', 'local_qrcurp'),1));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/confirmemailgeneral', get_string('confirmemailgeneral', 'local_qrcurp'),
            get_string('confirmemailinfogeneral', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/confirmemailexternos', get_string('confirmemailexternos', 'local_qrcurp'),
            get_string('confirmemailinfoexternos', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/emailexterno', get_string('emailexterno', 'local_qrcurp'),
            get_string('emailexternoinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/sampleregister', get_string('sampleregister', 'local_qrcurp'),
            get_string('sampleregisterinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configtext('local_qrcurp/defaultcategoryid', get_string('defaultcategoryid', 'local_qrcurp'),
            get_string('defaultcategoryidinfo', 'local_qrcurp'),'', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_qrcurp/defaultcourseid', get_string('defaultcourseid', 'local_qrcurp'),
            get_string('defaultcourseidinfo', 'local_qrcurp'),'', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_qrcurp/defaultgroupid', get_string('defaultgroupid', 'local_qrcurp'),
            get_string('defaultgroupidinfo', 'local_qrcurp'),'', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_qrcurp/adminsite', get_string('adminsite', 'local_qrcurp'),
            get_string('adminsiteinfo', 'local_qrcurp'), 'admin', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_qrcurp/mailsupport', get_string('mailsupport', 'local_qrcurp'),
            get_string('mailsupportinfo', 'local_qrcurp'),'', PARAM_RAW, 80));
        $settings->add(new admin_setting_configtext('local_qrcurp/dbcatalogoshost', get_string('dbcatalogoshost', 'local_qrcurp'),
            get_string('dbcatalogoshostinfo', 'local_qrcurp'), '', PARAM_HOST, 100));
        $settings->add(new admin_setting_configtext('local_qrcurp/dbcatalogos', get_string('dbcatalogos', 'local_qrcurp'),
            get_string('dbcatalogosinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configpasswordunmask('local_qrcurp/dbcatalogosuser', get_string('dbcatalogosuser', 'local_qrcurp'),
            get_string('dbcatalogosuserinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configpasswordunmask('local_qrcurp/dbcatalogospass', get_string('dbcatalogospass', 'local_qrcurp'),
            get_string('dbcatalogospassinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/showuniquecourses', get_string('showuniquecourses', 'local_qrcurp'),
            get_string('showuniquecoursesinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/showuniquecourseslist', get_string('showuniquecourseslist', 'local_qrcurp'),
            get_string('showuniquecourseslistinfo', 'local_qrcurp'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/coursealphaorder', get_string('coursealphaorder', 'local_qrcurp'),
            get_string('coursealphaorderinfo', 'local_qrcurp'),1));
       $settings->add(new admin_setting_configcheckbox('local_qrcurp/onegroupattime', get_string('onegroupattime', 'local_qrcurp'),
            get_string('onegroupattimeinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/groupsalredycreated', get_string('groupsalredycreated', 'local_qrcurp'),
            get_string('groupsalredycreatedinfo', 'local_qrcurp'),1));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/creategroupenrol', get_string('creategroupenrol', 'local_qrcurp'),
            get_string('creategroupenrolinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configtext('local_qrcurp/idcreategroup', get_string('idcreategroup', 'local_qrcurp'),
            get_string('idcreategroupinfo', 'local_qrcurp'), '', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_qrcurp/nameplataform', get_string('nameplataform', 'local_qrcurp'),
            get_string('nameplataforminfo', 'local_qrcurp'), '', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_qrcurp/nameexternal', get_string('nameexternal', 'local_qrcurp'),
            get_string('nameexternalinfo', 'local_qrcurp'), '', PARAM_RAW, 50));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/counterviews', get_string('counterviews', 'local_qrcurp'),
            get_string('counterviewsinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/validaterenapo', get_string('validaterenapo', 'local_qrcurp'),
            get_string('validaterenapoinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/acceptnotvalidaterenapo', get_string('acceptnotvalidaterenapo', 'local_qrcurp'),
            get_string('acceptnotvalidaterenapoinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configcheckbox('local_qrcurp/acceptcoursesdbexternal', get_string('acceptcoursesdbexternal', 'local_qrcurp'),
            get_string('acceptcoursesdbexternalinfo', 'local_qrcurp'),0));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/accessbyroltocourse', get_string('accessbyroltocourse', 'local_qrcurp'),
            get_string('accessbyroltocourseinfo', 'local_qrcurp'), '', PARAM_RAW, 40));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/accessbyrolenrol', get_string('accessbyrolenrol', 'local_qrcurp'),
            get_string('accessbyrolenrolinfo', 'local_qrcurp'), '', PARAM_RAW, 40));
        $settings->add(new admin_setting_configtextarea('local_qrcurp/registeruserinothersite', get_string('registeruserinothersite', 'local_qrcurp'),
            get_string('registeruserinothersiteinfo', 'local_qrcurp'), '', PARAM_RAW, 20));
    }
}
