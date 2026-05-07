<?php
/**
 * @package local_qrcurp
 * @author  Luis_Felipe
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/../../../config.php');
global $CFG;
require_once($CFG->libdir.'/moodlelib.php');


function enviaCorreo($iduser,$typemail ='',$alias='',$idcourse='',$idgroup='',$nameCategory=''){
    global $CFG;

//    require_once($CFG->wwwroot."/local/qrcurp/globalVariables.php");

    $NAMEPLATAFORMQRCURP = get_config('local_qrcurp','nameplataform');
    global $DB,$CFG;
    if($nameCategory == ''){
        $nameCursos = get_config('local_qrcurp','defaultnamecategory');
    }
    $urlprincipal =$CFG->wwwroot.'/login/index.php';
    $urlcourses =$CFG->wwwroot.'/course/view.php?id=';

    $from = $DB->get_record('user', array('id' => "$iduser"));
    $para = $from->email;
    $idusurio = $from->id;
    $primeracceso = $from->firstaccess;
    $ultimoacceso = $from->lastaccess;
    $correoconfirmado = $from->confirmed;
    $supportuser = \core_user::get_support_user();
    $user = get_complete_user_data('id', $idusurio);
    $correoexterno = get_config('local_qrcurp','emailexterno');
    $message = get_string('mailwelcome', 'local_qrcurp');
    if($typemail == 0){
        //YA ESTÁ REGISTRADO Y SOLO ENVIA SU NOMBRE DE USUARIO Y CONTRASEÑA
        $from = $DB->get_record('user', array('id' => "$iduser"));
        $alias = $from->idnumber; //cambiar por el campo donde se guardara la contraseña
        $subject = get_string('mailsubjectwelcomeplatform', 'local_qrcurp', $NAMEPLATAFORMQRCURP);
        $mensajehtml = get_string('mailbodywelcomeplatform', 'local_qrcurp', (object)[
            'firstname' => $from->firstname, 'lastname' => $from->lastname, 'category' => $nameCursos,
            'url' => $urlprincipal, 'username' => $from->username, 'password' => $alias
        ]);
        $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
    }
    if($typemail == 2){
        //SE REGISTRA EN UN CURSO Y ENVIA NOMBRE DE USUARIO Y CONTRSASEÑA
        $from = $DB->get_record('user', array('id' => "$iduser"));
        if($alias == ''){
            $alias = $from->idnumber; //cambiar por el campo donde se guardara la contraseña
        }
        $cursoinscrito = $DB->get_record('course', array('id' => "$idcourse"),'fullname');
        $nombrecurso = $cursoinscrito->fullname;
        $urlcourses = $urlcourses.$idcourse; //URL con el id de ese curso
        $subject = get_string('mailsubjectwelcomecourseplatform', 'local_qrcurp', (object)['course' => $nombrecurso, 'platform' => $NAMEPLATAFORMQRCURP]);
        $mensajehtml = get_string('mailbodywelcomecourse', 'local_qrcurp', (object)[
            'firstname' => $from->firstname, 'lastname' => $from->lastname, 'course' => $nombrecurso,
            'url' => $urlcourses, 'username' => $from->username, 'password' => $alias
        ]);
        //$mensajehtml = "Hola que tal ..";
        if($correoexterno == 1) {
            $url = 'https://'.$_SERVER['HTTP_HOST'];
            $urlcorreo ='/local/qrcurp/mail/sendmail/index.php/Mail/enviarCorreo/'.$para.'/'.$typemail.'/'.$subject.'/'.$from->firstname .$from->lastname.'/'.$nombrecurso.'/'.$idcourse.'/'.$from->username.'/'.$alias;
            $url = $url.$urlcorreo;
            redirect($urlcorreo);
        }else{
            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);

        }
    }
    if($typemail == 3){
        //SE REGISTRA EN UN CURSO Y EN UN GRUPO Y ENVIA USUARIO Y CONTRASEÑA
        $rutapdf = $CFG->dataroot. "/docs/RMS_RL2019_Guia_Navegacion.pdf";

        $from = $DB->get_record('user', array('id' => "$iduser"));
        if($alias == ''){
            $alias = $from->idnumber; //cambiar por el campo donde se guardara la contraseña
//            if(get_string('local_qrcurp','confirmemail' == 1)){
//                $alias = $from->secret; //cambiar por el campo donde se guardara la contraseña
//            }
        }
        $cursoinscrito = $DB->get_record('course', array('id' => "$idcourse"),'fullname');
        $gruposinscrito = $DB->get_record('groups', array('id' => "$idgroup"),'name');
        $nombregrupo = $gruposinscrito->name;
        $nombrecurso = $cursoinscrito->fullname;
        $urlcourses = $urlcourses.$idcourse; //URL con el id de ese curso
        $subject = get_string('mailsubjectwelcomecourse', 'local_qrcurp', $nombrecurso);
        $mensajehtml = get_string('mailbodywelcomecoursegroup', 'local_qrcurp', (object)[
            'firstname' => $from->firstname, 'lastname' => $from->lastname, 'course' => $nombrecurso, 'group' => $nombregrupo,
            'url' => $urlcourses, 'username' => $from->username, 'password' => $alias
        ]);
        //$mensajehtml = "Estimada/o <strong> $from->firstname $from->lastname </strong>."."<br><br>"."Te damos la más cordial bienvenida a la comunidad de práctica de $nombrecurso, en el grupo de $nombregrupo. "."<br><br>"."Tus datos de acceso son los siguientes : <br><br> <strong>URL: </strong> <a href='$urlcourses' target='_blank'>$urlcourses</a> <br> <strong>Nombre de usuario: </strong> $from->username <br> <strong>Contraseña: </strong> $alias";
        //$mensajehtml = "Hola que tal ..";
        if($correoexterno == 1) {
            $url = 'https://'.$_SERVER['HTTP_HOST'];
            $urlcorreo ='/local/qrcurp/mail/sendmail/index.php/Mail/enviarCorreo/'.$para.'/'.$typemail.'/'.$subject.'/'.$from->firstname .$from->lastname.'/'.$nombrecurso.'/'.$nombregrupo.'/'.$idcourse.'/'.$from->username.'/'.$alias;
            $url = $url.$urlcorreo;
            redirect($urlcorreo);
        }else{
//            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml,$rutapdf,$emailsNames,true);
            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
        }
    }
    if($typemail == 4){
        //SE REGISTRA A LA PLATAFORMA Y REQUIERE CONFIRMACIÓN PARA CONTINUAR CON SU REGISTRO EN CONFIRM.PHP
        $from = $DB->get_record('user', array('id' => "$iduser")); //id de usuario para encontrar ese usuario
        $username = $from->username;
        if($alias == ''){
            $alias = $from->idnumber; //cambiar por el campo donde se guardara la contraseña(actualmente se guarda en idnumber)
        }
        $secretConfirm = $from->secret;
        $cursoinscrito = $DB->get_record('course', array('id' => "$idcourse"),'fullname');
        $gruposinscrito = $DB->get_record('groups', array('id' => "$idgroup"),'name');
        $nombregrupo = $gruposinscrito->name;
        $nombrecurso = $cursoinscrito->fullname;
        $urlconfirmusers = $CFG->wwwroot.'/local/qrcurp/confirm.php?data='.$secretConfirm.'/'.$username; //URL de confirmación
//        $urlcourses = $urlcourses.$idcourse; //URL con el id de ese curso
        $subject = get_string('mailsubjectconfirmregistration', 'local_qrcurp', $nameCategory);
        $mensajehtml = get_string('mailbodyconfirmregistration', 'local_qrcurp', (object)[
            'firstname' => $from->firstname, 'lastname' => $from->lastname, 'category' => $nameCategory, 'url' => $urlconfirmusers
        ]);
        //$mensajehtml = "Hola que tal ..";
        if($correoexterno == 1) {
            $url = 'https://'.$_SERVER['HTTP_HOST'];
            $urlcorreo ='/local/qrcurp/mail/sendmail/index.php/Mail/enviarCorreo/'.$para.'/'.$typemail.'/'.$subject.'/'.$nameCategory.'/'.$secretConfirm.'/'.$username;
            $url = $url.$urlcorreo;
            redirect($urlcorreo);
        }else{
            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
//            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml,$rutapdf,"RMS_RL2019_Guia_Navegacion.pdf",true);
        }
    }
    if($typemail == 5){
        //recuperar usuario y contraseña quedaría
        $nombre = $idgroup;
        $username = $idcourse;
        $subject = get_string('mailsubjectrecovercredentials', 'local_qrcurp', $nameCursos);
        $mensajehtml = get_string('mailbodyrecovercredentials', 'local_qrcurp', (object)[
            'name' => $nombre, 'platform' => $NAMEPLATAFORMQRCURP, 'url' => $CFG->wwwroot, 'username' => $username, 'password' => $alias
        ]);

        if($correoexterno == 1) {
            echo 'Correo externo';
            $url = 'https://'.$_SERVER['HTTP_HOST'];
            $urlcorreo ='/local/qrcurp/mail/sendmail/index.php/Mail/enviarCorreo/'.$para.'/'.$typemail.'/'.$subject.'/'.$nombre.'/'.$username.'/'.$alias;
            $url = $url.$urlcorreo;
            redirect($urlcorreo);
        }else{
            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
        }
    }
    if($typemail == 6){
        //Para cuando se matricula a un nuevo curso a un usuario ya registrado.
        $from = $DB->get_record('user', array('id' => "$iduser"));
        if($alias == ''){
            $alias = $from->idnumber; //cambiar por el campo donde se guardara la contraseña
        }
        $cursoinscrito = $DB->get_record('course', array('id' => "$idcourse"),'fullname');
        $gruposinscrito = $DB->get_record('groups', array('id' => "$idgroup"),'name');
        $nombregrupo = $gruposinscrito->name;
        $nombrecurso = $cursoinscrito->fullname;
        $urlcourses = $urlcourses.$idcourse; //URL con el id de ese curso
        $subject = get_string('mailsubjectwelcomecourseplatformshort', 'local_qrcurp', (object)['course' => $nombrecurso, 'platform' => $NAMEPLATAFORMQRCURP]);
        $mensajehtml = get_string('mailbodywelcomereenrolcourse', 'local_qrcurp', (object)[
            'firstname' => $from->firstname, 'lastname' => $from->lastname, 'course' => $nombrecurso, 'group' => $nombregrupo,
            'url' => $urlcourses, 'username' => $from->username, 'password' => $alias, 'platform' => $NAMEPLATAFORMQRCURP
        ]);
        $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
    }
    return $send;

}
