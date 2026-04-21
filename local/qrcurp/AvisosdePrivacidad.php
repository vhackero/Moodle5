<?php
require_once(__DIR__.'/../../config.php');

function avisoDePrivacidad($idcategoria=0){
    $AVISODEPRIVACIDAD = '';
    $customnotice = trim((string) get_config('local_qrcurp', 'privacynoticehtml'));
    if ($customnotice !== '') {
        return '<div id="texto-terminos-condiciones" style="border: 1px solid #6b5d4e; height: 15rem; overflow: auto; padding: 10px;">'.$customnotice.'</div>';
    }

    if($idcategoria == 0){
        $AVISODEPRIVACIDAD = ' <div id="texto-terminos-condiciones"  style="border: 1px solid #6b5d4e; height: 100px; overflow: auto; padding: 10px;">
                            <h2 class="bottom-buffer">Aviso de Privacidad Simplificado de <a href="https://www.gob.mx" target="_blank">gob.mx</a></h2>&nbsp;
                            <p>La Coordinación de Estrategia Digital Nacional de la Oficina de la Presidencia de la Repúbica es la responsable del tratamiento de los datos personales que se recolectan de forma general a través de gob.mx.</p>
                            <p>Esto no incluye aquellos datos que de forma específica son recopilados por las dependencias y entidades que dentro de gob.mx alojan sus sitios, micrositios y redes sociales; quienes son responsables por los mismos.</p>
                            <p>Los datos personales que se recaban por gob.mx serán utilizados con la finalidad de hacer llegar el boletín de gob.mx.</p>
                            <p>Si desea conocer nuestro aviso de privacidad integral, lo podrá consultar en el portal  <a href="https://www.gob.mx/aviso_de_privacidad" target="_blank">www.gob.mx/privacidadintegral</a></p>
                             </div>';
    }
    if($idcategoria ==1 ){
        $AVISODEPRIVACIDAD = ' <div id="texto-terminos-condiciones"  style="border: 1px solid #6b5d4e; height: 15rem; overflow: auto; padding: 10px;">
                            <h2 class="bottom-buffer">Aviso de privacidad simplificado<br>Cursos Autogestivos<br>
                                Universidad Abierta y a Distancia de México</a></h2>&nbsp;
                            <p>La Universidad Abierta y a Distancia de México (UnADM), es la responsable del tratamiento de los datos personales que nos proporciones a través del en el micrositio <a style="text-decoration: underline blue; color: blue" target="_blank" href="https://extension.unadmexico.mx/autogestivos/inicio.html">https://extension.unadmexico.mx/autogestivos/inicio.html</a>.</p>
                            <p>Los datos personales que se recopilan de los participantes son utilizados para los siguientes fines: generar un registro personal del participante que permita la gestión individual de su capacitación, soporte técnico, acceso electrónico a la aplicación móvil, generación de constancias, verificación y validación de datos de participación y acreditación de los cursos, elaboración de reportes y estadísticas.</p>
                            <p>No se realizan transferencias de datos personales, salvo aquéllas que sean necesarias para atender requerimientos de información de una autoridad competente, que estén debidamente fundados y motivados o aquellas en el ejercicio de las atribuciones encomendadas a esta Casa de Estudios, de conformidad con lo establecido en la Ley General de Protección de Datos Personales en Posesión de Sujetos Obligados.</p>
                            <p>El titular puede manifestar su negativa para el tratamiento de sus datos personales para finalidades y transferencias que requieren su consentimiento, al momento en que le son requeridos.</p>
                            <p>Si desea conocer nuestro aviso de privacidad integral, puede consultarlo en nuestro portal web institucional <a style="text-decoration: underline blue; color: blue" target="_blank" href="www.unadmexico.mx">www.unadmexico.mx</a>.</p>
                            <p>Está de acuerdo con el tratamiento de sus datos personales:</p>
                            <p style="text-align: right">Última actualización 18/01/2023.</p>
                            </div>';
    }
    return $AVISODEPRIVACIDAD;
}
