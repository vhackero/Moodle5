<?php

require ('../../config.php');

//nombre de usuario original
$usernames = ['elizabeth.ruiz','AL12523037'];
//$usernames = ['luis.alcocer'];
//nuevo nombre de usuario
$newusernames = ['DL15RURE00847','DL20VEHP00025'];
//cambio de nombre de usuario

$updateusers = chageUserName($usernames,$newusernames);

function sendEmail($username)
{
    $completeinfo = get_complete_user_data('username',$username);
    $supportuser = \core_user::get_support_user();
    $user = new stdClass();
    $user->email = $completeinfo->email;
    $user->id = -99;
    $username = $completeinfo->username;
    $alias = $completeinfo->idnumber;
    $subject = " Actualización de datos de acceso";
    $mensajehtml = "Estimada/o participante"."<br><br>"."Recibes este mensaje porque estás inscrito en uno o más cursos de extensión universitaria y/o educación continua de la UnADM. "."<br><br>"."Con el fin de homologar tus credenciales de acceso en las diferentes plataformas que ofertan dichos cursos, se actualizó tu nombre de usuario con el que tienes asignado en SIGE. Tus datos actualizados son los siguientes: <br><br> <strong>Nombre de usuario: </strong> $username <br> <strong>Contraseña: </strong> $alias<br><br> En caso de presentar inconvenientes por favor, levanta un ticket en mesa de ayuda.<br><br> <hr>  #OrgulloUnADM <br>";
    $message = $mensajehtml;
    $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);

}

function chageUserName($usernames, $newusernames)
{
    global $CFG,$DB;
    for($i =0 ; $i<sizeof($usernames); $i++) {
        $finduser = $DB->get_record('user', array('username' => $usernames[$i]));
        if ($finduser) {
            $finduser->username = strtolower($newusernames[$i]);
            $DB->update_record('user',$finduser);
            sendEmail($newusernames[$i]);
        } else {
            return false;
        }
    }
}

//envio de correo

