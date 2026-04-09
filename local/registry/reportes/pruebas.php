function uploadImgBase64 ($base64, $name){

global $CFG;
// decodificamos el base64
$datosBase64 = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
// definimos la ruta donde se guardara en el server
$path= $_SERVER['DOCUMENT_ROOT'].'/moodle/local/registry/reportes/img/'.$name;
// guardamos la imagen en el server
if(!file_put_contents($path, $datosBase64)){
// retorno si falla
return false;
}
else{
// retorno si todo fue bien
return true;
}

}

// llamamos a la funcion uploadImgBase64( img_base64, nombre_fina.png)
$ruta = 'reporte_'.date('d_m_Y').'.png';
$upload = uploadImgBase64($_POST['imagen'],$ruta);