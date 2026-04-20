<?php
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once('mail/index.php');//Se incluye el archivo que enviará el correo


define('GROUPENTIDAD',10001);
define('GROUPMUNICIPIO',10002);
define('GROUPENTIDADMUNICIPIO',10003);
define('GROUPROL',10004);
define('GROUPOCUPACION',10005);
define('GROUPOA',10006);
define('GROUPOB',10007);
define('GROUPOC',10008);
define('GROUPOD',10009);
define('GROUPOE',100010);
define('GROUPOF',100011);
define('DEFAULTNAMEGROUP','GRUPO');

function SetCourseGroupMoodle($iduserinsert,$idcourse='',$idcreategroup=''){

    global $DB, $CFG;
    $seEnviaCorreo = false;
    $rolestudent = get_config('local_qrcurp','rolstudent');//Rol con el que se agregara el nuevo usuario
    $estadoresidencia = "estado_residencia";// El nombre es el que se agreaga en los datos de usuario en el formulario de registro moodle
    $ocupaciondb = "ocupacion";// El nombre es el que se agreaga en los datos de usuario en el formulario de registro moodle
    $camporolname = "rol_name";// El nombre es el que se agreaga en los datos de usuario en el formulario de registro moodle

    if ($idcourse != '') {

        //verifica si existe el curso con ese id
        $verificursoid = $DB->get_record('course',array('id'=>$idcourse));

        if($verificursoid){
            //El curso es valido y continua con el procesos
        }else{
            print_error("El id del curso no es válido.");
        }
        //SE REGISTRA EN EL COURSE
        //ID DEL ADMINISTRADOR
        $nameadmin = get_config('local_qrcurp','adminsite'); //NOMBRE DEL ADMINISTRADOR DEL SITIO
        if($nameadmin == ''){
            $nameadmin = "admin";
        }
        $verificaidadmin = $DB->get_record('user', array('username' => $nameadmin));
        $idadminuser = $verificaidadmin->id;
        //echo $idadminuser;
        if ($verificaidadmin) {
            //SI EXISTE EL ADMINISTRADOR CON ESE ID
            //ID DEL USUARIO REGISTRADO
            $consultacontex=$DB->get_record('context',array('instanceid'=>$idcourse, 'contextlevel'=> '50' ));
            $idcontext = $consultacontex->id;
            if($consultacontex) {
                $consultarole = $DB->get_record('role_assignments', array('contextid' => $idcontext, 'userid' => $iduserinsert));

            }else{
                print_error("No se logró determinar el contexto.");
            }
            if($consultarole){
                //El usuario ya esta matriculado por alguna razón
                print_error("El usuario ya se encuentra matrículado.");
            }else{
                $fecha = time();
                //ID DEL CURSO AL QUE SE REGITRARA
                //$verificaidrol = $DB->get_record('enrol', array('enrol' => "manual", "courseid" => 3));
                $verificaidrol = $DB->get_record('enrol', array('enrol' => "manual", "courseid" => $idcourse));
                $idrolcourse = $verificaidrol->id;
                //echo $idrolcourse;
                //REGISTRO EN ROLE ASIGMENTS
                $registroroleasigments = new stdClass();
                $registroroleasigments->roleid = $rolestudent;
                $registroroleasigments->contextid =$idcontext;
                $registroroleasigments->userid = $iduserinsert;
                $registroroleasigments->timemodified = $fecha;
                $registroroleasigments->modifierid = $idadminuser;

                $insertrole= $DB->insert_record('role_assignments',$registroroleasigments);
                if($insertrole){

                }else{
                    print_error("No se logró insertar el dato en role_assigments.");
                }

                //REGISTRO DEL USUARIO EN ESE CURSO
                $fecha = time();
                $registrocourse = new stdClass();
                $registrocourse->status = 0;
                $registrocourse->enrolid = $idrolcourse;
                $registrocourse->userid = $iduserinsert;
                $registrocourse->modifierid = $idadminuser;
                $registrocourse->timestart = $fecha;
                $registrocourse->timeend = 0;
                $registrocourse->timecreated = $fecha;
                $registrocourse->timemodified = $fecha;

                $insertauserincourse = $DB->insert_record('user_enrolments', $registrocourse);

                if ($insertauserincourse) {
                    //VARIFICANDO SI EXISTE UN GRUPO CON EL NOMBRE, SI NO LO CREA Y LO INSERTA EN EL GRUPO
                    if($idcreategroup != '') {
                        $existidgroup = groups_group_exists ($idcreategroup);
                        if($existidgroup){
                            //echo "existe el grupo con el id:".$idcreategroup."<br>";
                            //echo "Usuario agregado a el grupo:".$idcreategroup."<br>";
                            //AGREGA A EL USUARIO A EL GRUPO
                            $agregausertogroup = groups_add_member($idcreategroup, $iduserinsert);
                            if($agregausertogroup){
                                $enviacorreo = 3; //usuario agregado con éxito a un grupo y un curso
                                $seEnviaCorreo = enviaCorreo($iduserinsert,$enviacorreo,'',$idcourse,$idcreategroup);//PARA ENVIAR EL CORREO CON EL USUARIO Y CONTRASEÑA
                                // echo "agregado con éxito al group";
                            }else{
                                print_error("No se logró agregar al usuario a un grupo.");
                            }
                            //die();

                        }else {
                            $fecha = time();

                            switch ($idcreategroup) {
                                case GROUPENTIDAD:
                                    //SI SE REQUIERE PARA OTRO SISTEMA QUE NOS ES MOODLE
                                    //if($estado != ''){
                                        $consultaidinforfield = $DB->get_record("user_info_field",array('shortname'=>$estadoresidencia));
                                        $iduserinfofield = $consultaidinforfield->id;
                                        $consultaestado = $DB->get_record('user_info_data',array('userid'=>$iduserinsert,'fieldid'=>$iduserinfofield));
                                        $estado = $consultaestado->data;
                                        $nombregroup = $estado."-".date("m")."-".date("y");
                                    //}else{
                                        //Ingresa el dato que trae en estado
                                        //$nombregroup = $estado;
                                    //}
                                    break;
                                case GROUPMUNICIPIO:
                                    $consultauser = $DB->get_record("user",array('id'=>$iduserinsert),'city');
                                    $municipio = $consultauser->city;
                                    $nombregroup = $municipio;
                                    break;
                                case GROUPENTIDADMUNICIPIO:
                                    $consultaidinforfield = $DB->get_record("user_info_field",array('shortname'=>$estadoresidencia));
                                    $iduserinfofield = $consultaidinforfield->id;
                                    $consultaestado = $DB->get_record('user_info_data',array('userid'=>$iduserinsert,'fieldid'=>$iduserinfofield));
                                    $estado = $consultaestado->data;
                                    $consultauser = $DB->get_record("user",array('id'=>$iduserinsert),'city');
                                    $municipio = $consultauser->city;
                                    $nombregroup = $estado . $municipio;
                                    break;
                                case GROUPROL:
                                    $consultaidinforfield = $DB->get_record("user_info_field",array('shortname'=>$camporolname));
                                    $iduserinfofield = $consultaidinforfield->id;
                                    $consultarolname = $DB->get_record('user_info_data',array('userid'=>$iduserinsert,'fieldid'=>$iduserinfofield));
                                    $rolname = $consultarolname->data;
                                    $nombregroup = $rolname;
                                    break;
                                case GROUPOCUPACION:
                                    $consultaidinforfield = $DB->get_record("user_info_field",array('shortname'=>$ocupaciondb));
                                    $iduserinfofield = $consultaidinforfield->id;
                                    $consultaocupacion = $DB->get_record('user_info_data',array('userid'=>$iduserinsert,'fieldid'=>$iduserinfofield));
                                    $ocupacion = $consultaocupacion->data;
                                    $nombregroup = $ocupacion;
                                    break;
                                case GROUPA:
                                    $nombregroup = "GRUPO A";
                                    break;
                                case GROUPB:
                                    $nombregroup = "GRUPO B";
                                    break;
                                case GROUPC:
                                    $nombregroup = "GRUPO C";
                                    break;
                                case GROUPD:
                                    $nombregroup = "GRUPO D";
                                    break;
                                case GROUPE:
                                    $nombregroup = "GRUPO E";
                                    break;
                                case GROUPF:
                                    $nombregroup = "GRUPO F";
                                    break;
                                default:
                                    $nombregroup = DEFAULTNAMEGROUP;
                            }
                            $existegrupo = groups_get_group_by_name($idcourse, $nombregroup);
                            if ($existegrupo == false) {
                                //echo "NO existe un grupo con ese nombre";
                                //se creará al gruop
                                $creategroup = new stdClass();
                                $creategroup->courseid = $idcourse;
                                //$creategroup->idnumber = someidnumber;
                                $creategroup->name = $nombregroup;
                                //$creategroup->description = 'group description';
                                $creategroup->descriptionformat = 1;
                                $creategroup->picture = 0;
                                $creategroup->timecreated = $fecha;
                                $creategroup->timemodified = $fecha;
                                $newgroupid = groups_create_group($creategroup);

                                //consulta el id del groupo creado
                                $recordgroup = $DB->get_record('groups', array('courseid' => $idcourse, 'name' => $nombregroup));
                                $idgroup = $recordgroup->id;
                                $recordexistuseringroup = $DB->get_record('groups_members', array('groupid' => $idgroup, 'userid' => $iduserinsert));
                                if ($recordexistuseringroup) {
                                    //echo "Uusario ya a sido agregado anteriormente";
                                } else {
                                    //Agrega el usuario al group
                                    $agregausertogroup = groups_add_member($idgroup, $iduserinsert);
                                    if ($agregausertogroup) {
//                                        //TODO AGREGADO PARA ACTUALIZAR EL CAMPO POR EL ID DEL GRUPO QUE SE CREO CON ANTERIORIDAD
//                                        $record = new stdClass();
//                                        $record->id = $iduserinsert;
//                                        $record->department = $idgroup;
//                                        $confirmedrecord = $DB->update_record('user', $record);
                                        $enviacorreo = 3; //usuario agregado con éxito a un grupo y un curso
                                        $seEnviaCorreo = enviaCorreo($iduserinsert,$enviacorreo,'',$idcourse,$idgroup);//PARA ENVIAR EL CORREO CON EL USUARIO Y CONTRASEÑA
                                        // echo "agregado con éxito al group";
                                    } else {
                                        print_error("No se logró agregar al usuario a un grupo.");
                                    }
                                    //die();
                                }

                            }else {
                                $recordgroup = $DB->get_record('groups', array('courseid' => $idcourse, 'name' => $nombregroup));
                                $idgroup = $recordgroup->id;
                                //echo "Ya existe el grupo con ese nombre";
                                //echo $idgroup;
                                $recordexistuseringroup = $DB->get_record('groups_members', array('groupid' => $idgroup, 'userid' => $iduserinsert));
                                if ($recordexistuseringroup) {
                                    //echo "Usuario ya a sido agregado anteriormente";
                                } else {
                                    //Agrega el usuario al group
                                    $agregausertogroup = groups_add_member($idgroup, $iduserinsert);
                                    if ($agregausertogroup) {
                                        $enviacorreo = 3; //usuario agregado con éxito a un grupo y un curso
                                        $seEnviaCorreo = enviaCorreo($iduserinsert,$enviacorreo,'',$idcourse,$idgroup);//PARA ENVIAR EL CORREO CON EL USUARIO Y CONTRASEÑA

                                        // echo "agregado con éxito al group";
                                    } else {
                                        print_error("No se logro agregar a el usuario a un grupo");
                                    }
                                    //die();
                                }

                            }
                        }
                    }
                        else{
                        //NO SE CREA EL GROUP
                        //echo "NO SE CREA EL GRUPO";
                        //Pero si fue exito el registro del curso
                        $enviacorreo = 2;//Registro exitos en un curso y un grupo
                            $seEnviaCorreo = enviaCorreo($iduserinsert,$enviacorreo,'',$idcourse);//PARA ENVIAR EL CORREO CON EL USUARIO Y CONTRASEÑA

                        }
                    //redirecciona a el curso en el que se registro
                    //$destination = "$CFG->wwwroot/course/view.php?id=$idcourse";
                    //$destination = "$CFG->wwwroot/my";
                    $message = "Usuario agregado con éxito.";
                    //redirect($destination, $message, null, \core\output\notification::NOTIFY_SUCCESS);
                    //redirect($destination);   // Bye!
                } else {
                    //No se pudo registrar el usuario en el curso
                }

            }

        }
        else {
            print_error("No se logro determinar el id del administrador, revisar que el username es admin");
        }
    } else {
        //SOLO se registra y redirecciona a el index?
    }
    return $seEnviaCorreo;
}

function addDataUserInfoFieldQrCurp($data,$namesdata){
    global $DB,$OUTPUT;

    for($i = 0; $i<sizeof($data); $i++) {
            $datainsert = new stdClass();
            $datainsert->shortname = $data[$i];
            $datainsert->name = $namesdata[$i];
            $datainsert->datatype = "text";
            $datainsert->desciptionformat = 1;
            $datainsert->categoryid = 1;
            $datainsert->locked= 1;
            $datainsert->param1= 30;
            $datainsert->param2= 2048;
            //Agregando todos los campos
            $insertTable = $DB->insert_record('user_info_field',$datainsert );
            if($insertTable){
                $message = "Listo";
                $messageAdd = "Agregando el campo: ".$namesdata[$i];
                \core\notification::info($messageAdd);
                \core\notification::success($message);

            }

    }
    echo "<div style='width: 100%; text-align: center'><a class='btn btn-info ' href='index.php'>Aceptar</div>";

}
