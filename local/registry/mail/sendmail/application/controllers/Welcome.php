<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {


	public function index()
	{
		$this->email();
		$this->load->view('welcome_message');
	}
	public function email() {

		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => '172.18.26.200',
			'smtp_port' => 25,
			'smtp_user' => 'retosciudadanos@mta.unadmexico.mx',
			'smtp_pass' => 'Kgp6g2xIsdEUOAJ72xakVOWZ',
			'charset' => 'utf-8',
			'priority' => 1
		);

		$CI = & get_instance();
		$CI->load->helper('url');
		$CI->load->library('session');
		$CI->config->item('base_url');

		$CI->load->library('email');

		$CI->email->initialize($config);

		$subject = 'Bienvenido a mi app';

		$msg = 'Mensaje de prueba';
		$email = 'luis.alcocer@nube.unadmexico.mx';
		$CI->email
			->from('retosciudadanos@mta.unadmexico.mx')
			->to($email)
			->subject($subject)
			->message($msg)
			->send();
	}
}
