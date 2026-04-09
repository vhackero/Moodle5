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

    // Create the new settings page
    // - in a local plugin this is not defined as standard, so normal $settings->methods will throw an error as
    // $settings will be NULL
    $settings = new admin_settingpage('local_registry', 'Configuración de REGISTRY');

    // Create
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext('local_registry/dbhost', get_string('dbhost', 'local_registry'),
            get_string('dbhostinfo', 'local_registry'), '', PARAM_URL, 30));
        $settings->add(new admin_setting_configtext('local_registry/dbport', get_string('dbport', 'local_registry'),
            get_string('dbportinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configtext('local_registry/dbname', get_string('dbname', 'local_registry'),
            get_string('dbnameinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configtext('local_registry/dbtable', get_string('dbtable', 'local_registry'),
            get_string('dbtableinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configpasswordunmask('local_registry/dbuser', get_string('dbuser', 'local_registry'),
            get_string('dbuserinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configpasswordunmask('local_registry/dbpass', get_string('dbpass', 'local_registry'),
            get_string('dbpassinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configcheckbox('local_registry/dbinsert', get_string('dbinsert', 'local_registry'),
            get_string('dbinsertinfo', 'local_registry'), 0));
        $settings->add(new admin_setting_configpasswordunmask('local_registry/dateregistro', get_string('dateregistro', 'local_registry'),
            get_string('dateregistroinfo', 'local_registry'), '', PARAM_URL, 30));
        $settings->add(new admin_setting_configtextarea('local_registry/textregistro', get_string('textregistro', 'local_registry'),
            get_string('textregistroinfo', 'local_registry'), '', PARAM_RAW, 500));
        $settings->add(new admin_setting_configtextarea('local_registry/dateperiodos', get_string('dateperiodos', 'local_registry'),
            get_string('dateperiodosinfo', 'local_registry'), '', PARAM_RAW, 500));
        $settings->add(new admin_setting_configtextarea('local_registry/dateduracionperidos', get_string('dateduracionperidos', 'local_registry'),
            get_string('dateduracionperidosinfo', 'local_registry'), '', PARAM_RAW, 500));
        $settings->add(new admin_setting_configcheckbox('local_registry/publicogeneral', get_string('publicogeneral', 'local_registry'),
            get_string('publicogeneralinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configcheckbox('local_registry/onlypublicogeneral', get_string('onlypublicogeneral', 'local_registry'),
            get_string('onlypublicogeneralinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configtextarea('local_registry/idcategories', get_string('idcategories', 'local_registry'),
            get_string('idcategoriesinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configtext('local_registry/rolteacher', get_string('rolteacher', 'local_registry'),
            get_string('rolteacherinfo', 'local_registry'), '', PARAM_ALPHANUM, 3));
        $settings->add(new admin_setting_configtext('local_registry/limitegroup', get_string('limitegroup', 'local_registry'),
            get_string('limitegroupinfo', 'local_registry'), '', PARAM_ALPHANUM, 5));
        $settings->add(new admin_setting_configtext('local_registry/studentxcategory', get_string('studentxcategory', 'local_registry'),
            get_string('studentxcategoryinfo', 'local_registry'), '', PARAM_ALPHANUM));
        $settings->add(new admin_setting_configtextarea('local_registry/studentxcategorytext', get_string('studentxcategorytext', 'local_registry'),
            get_string('studentxcategorytextinfo', 'local_registry'), '', PARAM_RAW));
        $settings->add(new admin_setting_configtext('local_registry/rolstudent', get_string('rolstudent', 'local_registry'),
            get_string('rolstudentinfo', 'local_registry'), '', PARAM_ALPHANUM, 3));
        $settings->add(new admin_setting_configcheckbox('local_registry/haygroupespera', get_string('haygroupespera', 'local_registry'),
            get_string('haygroupesperainfo', 'local_registry'),0));
        $settings->add(new admin_setting_configtext('local_registry/namegroupespera', get_string('namegroupespera', 'local_registry'),
            get_string('namegroupesperainfo', 'local_registry'), '', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_registry/defaultnamecategory', get_string('defaultnamecategory', 'local_registry'),
            get_string('defaultnamecategoryinfo', 'local_registry'),'', PARAM_RAW, 50));
        $settings->add(new admin_setting_configcheckbox('local_registry/confirmemail', get_string('confirmemail', 'local_registry'),
            get_string('confirmemailinfo', 'local_registry'),1));
        $settings->add(new admin_setting_configcheckbox('local_registry/confirmemailgeneral', get_string('confirmemailgeneral', 'local_registry'),
            get_string('confirmemailinfogeneral', 'local_registry'),0));
        $settings->add(new admin_setting_configcheckbox('local_registry/confirmemailexternos', get_string('confirmemailexternos', 'local_registry'),
            get_string('confirmemailinfoexternos', 'local_registry'),0));
        $settings->add(new admin_setting_configcheckbox('local_registry/emailexterno', get_string('emailexterno', 'local_registry'),
            get_string('emailexternoinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configcheckbox('local_registry/sampleregister', get_string('sampleregister', 'local_registry'),
            get_string('sampleregisterinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configtext('local_registry/defaultcategoryid', get_string('defaultcategoryid', 'local_registry'),
            get_string('defaultcategoryidinfo', 'local_registry'),'', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_registry/defaultcourseid', get_string('defaultcourseid', 'local_registry'),
            get_string('defaultcourseidinfo', 'local_registry'),'', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_registry/defaultgroupid', get_string('defaultgroupid', 'local_registry'),
            get_string('defaultgroupidinfo', 'local_registry'),'', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_registry/adminsite', get_string('adminsite', 'local_registry'),
            get_string('adminsiteinfo', 'local_registry'), 'admin', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_registry/mailsupport', get_string('mailsupport', 'local_registry'),
            get_string('mailsupportinfo', 'local_registry'),'', PARAM_RAW, 80));
        $settings->add(new admin_setting_configtext('local_registry/dbcatalogoshost', get_string('dbcatalogoshost', 'local_registry'),
            get_string('dbcatalogoshostinfo', 'local_registry'), '', PARAM_URL, 30));
        $settings->add(new admin_setting_configtext('local_registry/dbcatalogos', get_string('dbcatalogos', 'local_registry'),
            get_string('dbcatalogosinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configpasswordunmask('local_registry/dbcatalogosuser', get_string('dbcatalogosuser', 'local_registry'),
            get_string('dbcatalogosuserinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configpasswordunmask('local_registry/dbcatalogospass', get_string('dbcatalogospass', 'local_registry'),
            get_string('dbcatalogospassinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configcheckbox('local_registry/showuniquecourses', get_string('showuniquecourses', 'local_registry'),
            get_string('showuniquecoursesinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configtextarea('local_registry/showuniquecourseslist', get_string('showuniquecourseslist', 'local_registry'),
            get_string('showuniquecourseslistinfo', 'local_registry'), '', PARAM_RAW, 30));
        $settings->add(new admin_setting_configcheckbox('local_registry/coursealphaorder', get_string('coursealphaorder', 'local_registry'),
            get_string('coursealphaorderinfo', 'local_registry'),1));
       $settings->add(new admin_setting_configcheckbox('local_registry/onegroupattime', get_string('onegroupattime', 'local_registry'),
            get_string('onegroupattimeinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configcheckbox('local_registry/groupsalredycreated', get_string('groupsalredycreated', 'local_registry'),
            get_string('groupsalredycreatedinfo', 'local_registry'),1));
        $settings->add(new admin_setting_configcheckbox('local_registry/creategroupenrol', get_string('creategroupenrol', 'local_registry'),
            get_string('creategroupenrolinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configtext('local_registry/idcreategroup', get_string('idcreategroup', 'local_registry'),
            get_string('idcreategroupinfo', 'local_registry'), '', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_registry/nameplataform', get_string('nameplataform', 'local_registry'),
            get_string('nameplataforminfo', 'local_registry'), '', PARAM_RAW, 50));
        $settings->add(new admin_setting_configtext('local_registry/nameexternal', get_string('nameexternal', 'local_registry'),
            get_string('nameexternalinfo', 'local_registry'), '', PARAM_RAW, 50));
        $settings->add(new admin_setting_configcheckbox('local_registry/counterviews', get_string('counterviews', 'local_registry'),
            get_string('counterviewsinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configcheckbox('local_registry/validaterenapo', get_string('validaterenapo', 'local_registry'),
            get_string('validaterenapoinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configcheckbox('local_registry/acceptnotvalidaterenapo', get_string('acceptnotvalidaterenapo', 'local_registry'),
            get_string('acceptnotvalidaterenapoinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configcheckbox('local_registry/acceptcoursesdbexternal', get_string('acceptcoursesdbexternal', 'local_registry'),
            get_string('acceptcoursesdbexternalinfo', 'local_registry'),0));
        $settings->add(new admin_setting_configtextarea('local_registry/accessbyroltocourse', get_string('accessbyroltocourse', 'local_registry'),
            get_string('accessbyroltocourseinfo', 'local_registry'), '', PARAM_RAW, 40));
        $settings->add(new admin_setting_configtextarea('local_registry/accessbyrolenrol', get_string('accessbyrolenrol', 'local_registry'),
            get_string('accessbyrolenrolinfo', 'local_registry'), '', PARAM_RAW, 40));
        $settings->add(new admin_setting_configtextarea('local_registry/registeruserinothersite', get_string('registeruserinothersite', 'local_registry'),
            get_string('registeruserinothersiteinfo', 'local_registry'), '', PARAM_RAW, 20));
    }
}
