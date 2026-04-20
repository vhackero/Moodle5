<?php

//CONFIRMACIÓN PARA LOS USUARIOS REGISTRADOS.
//global $CFG,$PAGE;

require(__DIR__ . '/../../config.php');
require(__DIR__ . '/../../login/lib.php');
require_once($CFG->libdir . '/authlib.php');

require_once('lib.php');

//echo "importado.";die();

$data = optional_param('data', '', PARAM_RAW);  // Formatted as:  secret/username
$PAGE->set_url('/local/qrcurp/confirm.php');
$PAGE->set_context(context_system::instance());

/*if (!$authplugin = signup_get_user_confirmation_authplugin()) {
    throw new moodle_exception('confirmationnotenabled');
}*/
if (!empty($data) || (!empty($p) && !empty($s))) {

    if (!empty($data)) {
        $dataelements = explode('/', $data, 2); // Stop after 1st slash. Rest is username. MDL-7647
        $usersecret = $dataelements[0];
        $username = $dataelements[1];
    } else {
        $usersecret = $p;
        $username = $s;
    }
//    echo $usersecret;
    //Cambaindo los datos a confirmados en moodle
    //consulta si existe uyn usuario con esos datos
    $consultauser = $DB->get_record('user', array('username' => $username, 'secret' => $usersecret));
    if ($consultauser) {
        //Consulta para saber si aun hay cupo
        $totalUserGroup = (count(groups_get_members($consultauser->department, 'u.*')));
        $limitedegrupo =$limitedegrupo = get_config('local_qrcurp','limitegroup');

        $eslistaEspera = $DB->get_record("groups",array('id'=>$consultauser->department));
        $nombredelGrupo = $eslistaEspera->name;
        $nohaylimite =0;
        if(get_config("local_qrcurp","haygroupespera")==1){
            $nameListaEspera = get_config("local_qrcurp","namegroupespera");
            if(strstr("$nombredelGrupo","$nameListaEspera")){
                $nohaylimite = 1;
            }
            //todo agregar los nombres de grupos que tienn otro cupo al del límite de grupos
            if(strstr("$nombredelGrupo","cultura")){
                $limitedegrupo = 40; //cup para practica de la cultura
            }
        }
        if($nohaylimite == 0){
            if ($totalUserGroup >= $limitedegrupo+1) {
                $url = $CFG->wwwroot.'/login/index.php';
                redirect($url, "Lo sentimos, tu confirmación tardo demasiado y el grupo al que intentas registrarte ha superado el límite permitido.", null, \core\output\notification::NOTIFY_INFO);
                die();
                //Todo que pasasra con los usuarios que se registraron pero no confirmaron
            }
        }


        if ($consultauser->confirmed == 0) {
//            echo 'El usuario aun no esta confirmado';
            $record = new stdClass();
            $record->id = $consultauser->id;
            $record->confirmed = '1';
//            $record->secret = $consultauser->idnumber; //guarda la contraseña en secret
//            $record->idnumber = ''; //Se guradar la contraseña tanto en id number como en secret
            //guardando datos de curso y grupo en la tabla de info_field
            if ($consultauser->institution != '' || $consultauser->department != '') {
                //Nueva forma de matricular.
                $courseid = $consultauser->institution;
                $groupid = $consultauser->department;

//                //se optiene el id del id_course y idgruop
//                $verificaname = array('courseid', 'grouping'); //TODO los nombres tendran que ser los mismos que se agregan en los campos de usuario en moodle en base a la documentación
//                $recorduserdata = new stdClass();
//                $tam =sizeof($verificaname);
////                $tam = $DB->count_records("user_info_field"); //todo se agrega en caso de que existan mas campos de perfil de usuario que los que se usan en el
//                for ($i = 0; $i < $tam; $i++) {
//                    //Insertará los datos de el curso y grupo al que se vinculara el usuario ya confirmado
//                    $recolectaids = $DB->get_record('user_info_field', array('shortname' => $verificaname[$i]));
//                    if ($recolectaids) {
//                        if ($recolectaids->shortname == $verificaname[$i]) {
//                            $courseid = $consultauser->institution;
//                            $fielid = $recolectaids->id;
//                            $recorduserdata->data = $consultauser->institution; // Course id guardado temporalmente por el registro
//                        } else {
//                            $groupid = $consultauser->department;
//                            $fielid = $recolectaids->id;
//                            $recorduserdata->data = $consultauser->department; // Group id guardado temporalmente por el registro
//                        }
//                        $recorduserdata->userid = $consultauser->id;
//                        $recorduserdata->fieldid = $fielid;
//                        $recorduserdata->dataformat = '0';
//                        //Insertara los datos en la tabla
//                        $insertardata = $DB->insert_record('user_info_data', $recorduserdata);
//                    } else {
//                        $destination = "$CFG->wwwroot/login/index.php";
//                        $message = "ADMIN: Verificar que los nombres del apartado de moodle 'Campos de perfil del usuario' esten creados: courseid, grouping ";
//                        redirect($destination, $message, null, \core\output\notification::NOTIFY_WARNING);
//                    }
//                }
            }
            //Update en la tabla user para cambiar el usuario a verificado
            $confirmedrecord = $DB->update_record('user', $record);
            ($confirmedrecord) ? $confirmed = 1 : $confirmed = 0;
            if ($confirmed == 1) {
                if (!$user = get_complete_user_data('username', $username)) {
                    print_error('cannotfinduser', '', '', s($username));
                }

                if (!$user->suspended) {
                    complete_user_login($user);

                    \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

                    // Check where to go, $redirect has a higher preference.
                    if (!empty($redirect)) {
                        if (!empty($SESSION->wantsurl)) {
                            unset($SESSION->wantsurl);
                        }
                        redirect($redirect);
                    }
                }

                $PAGE->navbar->add(get_string("confirmed"));
                $PAGE->set_title(get_string("confirmed"));
                $PAGE->set_heading($COURSE->fullname);
                echo $OUTPUT->header();
                echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
                echo "<h3>" . get_string("thanks") . ", " . fullname($USER) . "</h3>\n";
                echo "<p>" . get_string("confirmed") . "</p>\n";
//                echo $OUTPUT->single_button(core_login_get_return_url(), get_string('continue'));
                //MODIFICACION QRCURP
                $idusuario = $consultauser->id;
                $nameCategory = $consultauser->address;
                if ($courseid != '') {
                    //echo "se registrara en el course que trae";
                    if ($groupid != '') {
                        SetCourseGroupMoodle($idusuario, $courseid, $groupid, $nameCategory);
                        $redirect = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
                        require_logout();
//                        redirect($redirect);
                        $message = "Usuario verificado con éxito, Revisa tu correo electrónico : $consultauser->email para revisar tus datos de acceso";
                        redirect($redirect, $message, 20, \core\output\notification::NOTIFY_SUCCESS);
                    } else {
                        SetCourseGroupMoodle($idusuario, $courseid);
                        $redirect = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
                        require_logout();
//                        redirect($redirect);
                        $message = "Usuario verificado con éxito, Revisa tu correo electrónico : $consultauser->email para revisar tus datos de acceso";
                        redirect($redirect, $message, 20, \core\output\notification::NOTIFY_SUCCESS);
                    }
                }
            }
        }
        $consultauser = $DB->get_record('user', array('username' => $username));
        if ($consultauser) {
            if ($consultauser->confirmed == 1) {
                $urltogo = $CFG->wwwroot . '/course/view.php?id=' . $consultauser->institution;
                $user = get_complete_user_data('username', $username);
                $PAGE->navbar->add(get_string("alreadyconfirmed"));
                $PAGE->set_title(get_string("alreadyconfirmed"));
                $PAGE->set_heading($COURSE->fullname);
                echo $OUTPUT->header();
                echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
                echo "<p>" . get_string("alreadyconfirmed") . "</p>\n";
                echo $OUTPUT->single_button($urltogo, get_string('courses'));
                echo $OUTPUT->box_end();
                echo $OUTPUT->footer();
                exit;
            }
        } else {
//            echo "el usuario no se encontro";
            print_error("errorwhenconfirming");
        }
    }
    else {
        $consultauser = $DB->get_record('user', array('username' => $username));
        if ($consultauser) {
            if ($consultauser->confirmed == 1) {
                $urltogo = $CFG->wwwroot . '/course/view.php?id=' . $consultauser->institution;
                $user = get_complete_user_data('username', $username);
                $PAGE->navbar->add(get_string("alreadyconfirmed"));
                $PAGE->set_title(get_string("alreadyconfirmed"));
                $PAGE->set_heading($COURSE->fullname);
                echo $OUTPUT->header();
                echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
                echo "<p>" . get_string("alreadyconfirmed") . "</p>\n";
                echo $OUTPUT->single_button($urltogo, get_string('courses'));
                echo $OUTPUT->box_end();
                echo $OUTPUT->footer();
                exit;
            }
        } else {
//            echo "el usuario no se encontro";
            print_error("errorwhenconfirming");
        }
    }
//    echo "El estado de confirmacion esta en ".$confirmed;
}
