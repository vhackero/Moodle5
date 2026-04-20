<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Mail extends CI_Controller {


	public function index()
	{
		$this->enviarCorreoRegistroCiudadanos();
		$this->load->view('comprueba_mail');
	}
	public function enviarCorreo($email='',$typemail='',$subject='',$dato1='',$dato2='',$dato3='',$dato4='',$dato5='',$dato6='',$dato7='')
		{
			$subject = rawurldecode($subject);
			$dato1 = rawurldecode($dato1);
			$dato2 = rawurldecode($dato2);
			$dato3 = rawurldecode($dato3);
			$dato4 = rawurldecode($dato4);

//			$email = 'piipeliin@gmail.com';

			$config = array(
				'protocol' => 'smtp',
				'smtp_host' => '172.18.26.200',
				'smtp_port' => 25,
				'smtp_user' => 'retosciudadanos@mta.unadmexico.mx',
				'smtp_pass' => 'Kgp6g2xIsdEUOAJ72xakVOWZ',
				'charset' => 'utf-8',
			);
			//'priority' => 1
			$url = 'https://'.$_SERVER['HTTP_HOST'];
			$CI = &get_instance();
			$CI->load->helper('url');
			//$CI->load->library('session');
			$CI->config->item('base_url');

			$CI->load->library('email');

			$CI->email->initialize($config);
			//VERIFICA EL TIPO DE CORREO
			if($typemail == 2){
				$urlcourses= $url.'/course/view.php?id='.$dato3; //URL de confirmación
				$mensajehtml = "Estimada/o <strong> $dato1 </strong>."."<br><br>"."Te damos la más cordial bienvenida a la comunidad de práctica de $dato2."."<br><br>"."Tus datos de acceso son los siguientes : <br><br> <strong>URL: </strong> <a href='$urlcourses' target='_blank'>$urlcourses</a> <br> <strong>Nombre de usuario: $dato4 </strong>  <br> <strong>Contraseña: </strong> $dato5";
			}
			if($typemail == 3){
				$urlcourses= $url.'/course/view.php?id='.$dato4; //URL de confirmación
				$mensajehtml = "Estimada/o <strong> $dato1 </strong>."."<br><br>"."Te damos la más cordial bienvenida al curso de $dato2, en el grupo de $dato3. "."<br><br>"."Tus datos de acceso son los siguientes : <br><br> <strong>URL: </strong> <a href='$urlcourses' target='_blank'>$urlcourses</a> <br> <strong>Nombre de usuario: </strong> $dato5 <br> <strong>Contraseña: </strong> $dato6";
			}
			if($typemail == 4){
				$urlconfirmusers = $url.'/local/qrcurp/confirm.php?data='.$dato2.'/'.$dato3; //URL de confirmación
				$mensajehtml = "Hola, <br><br> Se ha solicitado una nueva cuenta en '".$dato1."' utilizando su dirección de correo electrónico. <br> Para confirmar su nueva cuenta, vaya a esta dirección web: <br><br>  <strong><a href='$urlconfirmusers'>$urlconfirmusers</a></strong> <br><br>En la mayoría de los programas de correo, esto debería aparecer como un enlace azul en el que puedes hacer clic. Si eso no funciona, A continuación, corte y pegue la dirección en la dirección en la parte superior de la ventana de su navegador web. <br><br> Si necesita ayuda, póngase en contacto con el administrador del sitio, Administrador Usuario ";
			}
			if($typemail == 5){
				$mensajehtml = "Estimado $dato1 : <br><br>Hemos recibido una petición de recuperación de Nombre de usuario y/o Contraseña en el Club Virtual de Lenguas. <br><br> Estas son tus credenciales de acceso: <br><br> <strong>URL de comunidades de práctica:</strong> <a href='$url'>$url</a> <br> <strong>Nombre de usuario: </strong> $dato2 <br> <strong>Contraseña: </strong> $dato3 <br><br> Para iniciar tu sesión de práctica, accede a la URL de comunidades de práctica con las credenciales de acceso que te estamos enviando.<br><br> <hr>  Club Virtual de Lenguas <br>" ;
			}

			//->cc('retosciudadanos@mta.unadmexico.mx')
			$CI->email
				->from('retosciudadanos@mta.unadmexico.mx')
				->to($email)
				->subject($subject)
				->message($mensajehtml)
				->set_mailtype('html');
			if($CI->email->send(FALSE)){
				if($typemail == 2){
					$data["idcourse"] = $dato3;
					$this->load->view('comprueba_mail',$data);
				}if($typemail == 3){
					$data["idcourse"] = $dato4;
					$this->load->view('comprueba_mail',$data);
				}if($typemail == 4){
					$this->load->view('send-email-confirm-activated');
				}if($typemail == 5){
					$this->load->view('restore_password',$data);
				}
			}else {
				echo "fallo <br/>";
			}
		}


}
