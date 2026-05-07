<?php

/**
 * Cambia el numero de secciones de un curso
 *
 * @package    local
 * @author macuco juan.manuel.mp8@gmail.com
 * @since Moodle 2.7
 */

defined('MOODLE_INTERNAL') || die;
//echo $CFG->dirroot.'/course/lib.php';
require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/course/format/lib.php");
//require_once($CFG->dirroot.'/course/lib.php');
/**
 * core grades functions
 */
class local_sections extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function change_numsections_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'increase' => new external_value(PARAM_INT, 'Incrementar o decrementar el numero de secciones')
            )
        );

    }

    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function change_numsections($courseid, $increase) {
        global $CFG, $USER, $DB;
        $params = self::validate_parameters(self::change_numsections_parameters(),
            array('courseid' => $courseid, 'increase' => $increase));




        $coursecontext = context_course::instance($params['courseid']);

        try {
            self::validate_context($coursecontext);
        } catch (Exception $e) {
            $exceptionparam = new stdClass();
            $exceptionparam->message = $e->getMessage();
            $exceptionparam->courseid = $params['courseid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }

        require_capability('moodle/course:update', $coursecontext);



        $course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);
        $courseformatoptions = course_get_format($course)->get_format_options();


        $access = false;
        if (has_capability('moodle/course:update', $coursecontext)) {
            // Can view all user's grades in this course.
            $access = true;

        }

        if (!$access) {
            throw new moodle_exception('nopermissiontoviewgrades', 'error');
        }



        if (isset($courseformatoptions['numsections'])) {
            if ($increase) {
                // Add an additional section.
                $courseformatoptions['numsections']++;
            } else {
                // Remove a section.
                $courseformatoptions['numsections']--;
            }

            // Don't go less than 0, intentionally redirect silently (for the case of
            // double clicks).
            if ($courseformatoptions['numsections'] >= 0) {
                update_course((object)array('id' => $course->id,
                    'numsections' => $courseformatoptions['numsections']));
                return 1;
            }
        }

        return 0;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function change_numsections_returns() {
        return new external_value(
            PARAM_INT,
            'Un valor como  0  => OK, 1 => FAILED'
        );
    }




    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function get_section_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course'),
                'sectionid' => new external_value(PARAM_INT, 'Id de la seccion a obtener',VALUE_DEFAULT,null)
            )
        );

    }

    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function get_section($courseid, $sectionid) {
        global $CFG, $USER, $DB;
        $params = self::validate_parameters(self::get_section_parameters(),
            array('courseid' => $courseid, 'sectionid' => $sectionid));




        $coursecontext = context_course::instance($params['courseid']);

        try {
            self::validate_context($coursecontext);
        } catch (Exception $e) {
            $exceptionparam = new stdClass();
            $exceptionparam->message = $e->getMessage();
            $exceptionparam->courseid = $params['courseid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
        }

        require_capability('moodle/course:update', $coursecontext);

        $course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);

        $secciones = array();

        if($params['sectionid']){
            $section = $DB->get_record('course_sections', array('id' => $params['sectionid']), '*', MUST_EXIST);
            $sectionnum = $section->section;


            $sectioninfo = get_fast_modinfo($course)->get_section_info($sectionnum);

            $array1 = convert_to_array($sectioninfo);
            //-----------Quitar estos datos para no acomplejar la estructura de regreso
            unset($array1['conditionscompletion']);
            unset($array1['conditionsgrade']);
            unset($array1['conditionsfield']);
            //------------------
            $secciones[]=(object)$array1;//Convertir a objero la salida y agregarlo a la lista

        }else{
            $modinfo = get_fast_modinfo($course);
            $sectioninfo = $modinfo->get_section_info_all();
            foreach ($sectioninfo as $thissection) {
                $array1 = convert_to_array($thissection);
                //-----------Quitar estos datos para no acomplejar la estructura de regreso
                unset($array1['conditionscompletion']);
                unset($array1['conditionsgrade']);
                unset($array1['conditionsfield']);
                //------------------
                $secciones[]=(object)$array1;//Convertir a objero la salida y agregarlo a la lista
            }
        }
        return $secciones;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function get_section_returns() {
        /*
         * [id] => 1
    [section] => 0
    [name] =>
    [visible] => 1
    [summary] =>
    [summaryformat] => 1
    [availability] =>
    [available] => 1
    [availableinfo] =>
    [uservisible] => 1
    [sequence] => 3
    [course] => 2
         */
        return
            new external_multiple_structure(
                new external_single_structure(
                    array(
                        //'activityid' => new external_value(
                        //    PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                        'id'  => new external_value(PARAM_INT, 'Id de la seccion'),
                        'section'  => new external_value(PARAM_INT, 'Id del curso al que pertenece la seccion'),
                        'course'  => new external_value(PARAM_INT, 'Id del curso al que pertenece la seccion'),
                        'name' => new external_value(PARAM_TEXT, 'El nombre de la seccion'),
                        'summary' => new external_value(PARAM_RAW, 'HTML del resumen de la seccion'),
                        'summaryformat' => new external_value(PARAM_INT, 'Formato del sumario'),
                        'availability' => new external_value(PARAM_ALPHANUM, 'Disponibilidad'),
                        //'available' => new external_value(PARAM_INT, 'Disponible'),
                        //'availableinfo' => new external_value(PARAM_ALPHANUM, 'Info de disponibilidad'),
                        'visible' => new external_value(PARAM_INT, 'Si esta o no visible para los usuarios'),
                        'sequence' => new external_value(PARAM_TEXT, 'Secuencia dentro del curso'),
                    )
                )
            );
    }









    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function update_sections_parameters() {
        return new external_function_parameters(
            array(
                'sections' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            //'activityid' => new external_value(
                            //    PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                            'id'  => new external_value(PARAM_INT, 'Id de la seccion'),
                            'course'  => new external_value(PARAM_INT, 'Id del curso al que pertenece la seccion'),
                            'name' => new external_value(PARAM_TEXT, 'El nombre de la seccion'),
                            'summary' => new external_value(PARAM_RAW, 'HTML del resumen de la seccion',VALUE_DEFAULT,
                                '', NULL_NOT_ALLOWED),
                            'summaryformat' => new external_value(PARAM_INT, 'Formato del sumario',VALUE_DEFAULT,1,NULL_NOT_ALLOWED),
                            'availability' => new external_value(PARAM_ALPHANUM, 'Disponibilidad',VALUE_OPTIONAL,
                                '', NULL_NOT_ALLOWED),
                            'usedefaultname' => new external_value(PARAM_INT, 'Utilizar el valor por default para el nombre de la seccion',VALUE_OPTIONAL,
                                '', NULL_NOT_ALLOWED),
                            //'availableinfo' => new external_value(PARAM_ALPHANUM, 'Info de disponibilidad'),
                            'visible' => new external_value(PARAM_INT, 'Si esta o no visible para los usuarios',VALUE_DEFAULT,1,NULL_NOT_ALLOWED),
                            'sequence' => new external_value(PARAM_TEXT, 'Secuencia dentro del curso',VALUE_OPTIONAL,
                                '', NULL_NOT_ALLOWED),
                        )
                    )
                )
            )
        );

    }

    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function update_sections($sections) {
        global $CFG, $USER, $DB;
        $iteracion = 0;
        $params = self::validate_parameters(self::update_sections_parameters(),
            array('sections' => $sections));
        //CTIE RV001-18062025 LFAS Modificación para generar la estructura de las semanas correctamente desde SIGIE por captura erronea de los programas
        $nombrerealSection = array(
            2=>'Semana 1',
            3=>'Semana 2',
            4=>'Semana 3',
            5=>'Semana 4',
            6=>'Semana 5',
            7=>'Semana 6',
            8=>'Semana 7',
            9=>'Semana 8',
            10=>'Semana 9',
            11=>'Semana 10',
        );
        //FIN CTIE RV001
        foreach ($params['sections'] as $index => $section){
            $data = ((object)$section);

            //CTIE RV002-18062025 LFAS Modificación para generar la estructura de las semanas correctamente desde SIGIE por captura erronea de los programas
            if(!$nombrerealSection[$data->id]){
                continue;
            }else{
                $data->name = $nombrerealSection[$data->id];
            }
            //FIN CTIE RV002

            $coursecontext = context_course::instance($data->course);

            $course = course_get_format($data->course)->get_course();
            course_create_sections_if_missing($course, range(0, $course->numsections));


            $sectioninfo = get_fast_modinfo($data->course)->get_section_info($data->id);
            $data->id = $sectioninfo->id;

            $section = $DB->get_record('course_sections', array('id' => $sectioninfo->id), '*', MUST_EXIST);
            $sectionnum = $section->section;

            try {
                self::validate_context($coursecontext);
            } catch (Exception $e) {
                $exceptionparam = new stdClass();
                $exceptionparam->message = $e->getMessage();
                $exceptionparam->courseid = $params['courseid'];
                throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
            }

            require_capability('moodle/course:update', $coursecontext);

            $course = $DB->get_record('course', array('id' => $data->course), '*', MUST_EXIST);

            if (!empty($CFG->enableavailability)) {
                // Renamed field.
                $data->availability = $data->availabilityconditionsjson;
                unset($data->availabilityconditionsjson);
                if ($data->availability === '') {
                    $data->availability = null;
                }
            }

            //             CTIE LFAS 12/12/2023 para modificar el resumen de las secciones
            //$data->summary = '<div style="background: #F3FAFF; padding:30px;">'.$data->summary.'</div>';
//            $texto = "Objetivo General";
            $data->summary = '<div class="pleca-item">&nbsp;</div>';
//            $data->summary = '<div style="background: #F3FAFF; padding:30px;">'.'<h3 style="color:#296ab0; margin-bottom: 30px; text-shadow: rgba(0, 0, 0, 0.1) 2px 2px 2px;text-align:center;font-weight: 600;">'.$texto.'</h3>'.'<p align="center">'.$data->summary.'</p></div>';
            $idcursomodificando = $data->course;
            if($section->section == 1){
                $data->name = 'Planificación';
                $data->summary = '';
            }else if($section->section > 1){
                $iteracion++;
            }
            $data->availability = '{"op":"&","c":[],"showc":[]}';

            $consulta = "
            SELECT 1
            FROM mdl_label l
            INNER JOIN mdl_course_modules cm ON cm.instance = l.id
            INNER JOIN mdl_course_sections cs ON cs.id = cm.section
            WHERE l.name LIKE '%Objetivo específico%'
              AND l.course = $idcursomodificando
              AND cs.section = $section->section
              AND cm.module = (
                  SELECT id FROM mdl_modules WHERE name = 'label'
              )
            LIMIT 1
            ";
            $labelCreada = $DB->get_records_sql($consulta);
            if(!$labelCreada){
                local_sections::crearSimpleLabel($idcursomodificando, $section->section, "");
            }

            $DB->update_record('course_sections', $data);
            rebuild_course_cache($course->id, true);

            // ---------- Dejar visible o no la sección ---------------
            if (has_capability('moodle/course:sectionvisibility', $coursecontext)) {
                if (!$data->visible) {
                    set_section_visible($course->id, $hide, '0');
                }else if ($data->visible) {
                    set_section_visible($course->id, $show, '1');
                }
            }
            // ---------------------------------------------------------


            if (isset($data->section)) {
                // Usually edit form does not change relative section number but just in case.
                $sectionnum = $data->section;
            }
            course_get_format($course->id)->update_section_format_options($data);

            // Set section info, as this might not be present in form_data.
            if (!isset($data->section))  {
                $data->section = $sectionnum;
            }
            // Trigger an event for course section update.
            $event = \core\event\course_section_updated::create(
                array(
                    'objectid' => $data->id,
                    'courseid' => $course->id,
                    'context' => $coursecontext,
                    'other' => array('sectionnum' => $data->section)
                )
            );
            $event->trigger();


        }
        //Modificaciones para crear cronograma CTIE 26/12/2023 LFAS
        $cronograma = "SI";
        $texto = 'Cronograma';

        //valida si el cronogrma fue creado
        $consulta = "SELECT * FROM mdl_label WHERE name LIKE '%Cronograma Semanas%' AND course = ".$idcursomodificando;
        $cronogramacreado = $DB->get_records_sql($consulta);

        if ($cronograma == 'SI' AND !$cronogramacreado) {
            $nombresecciones = $DB->get_records('course_sections',array('course'=>$idcursomodificando));
            $contadorseccions = count($nombresecciones)-2;
            $namesections = array('section0');
            if($iteracion >= $contadorseccions ) {
                $conta = 0;
                foreach ($nombresecciones as $sectionsrecord) {
                    $conta++;
                    if ($conta > 2) {
                        array_push($namesections,$sectionsrecord->name);
                    }
                }
                $cronograma = local_sections::dividirFechas('', '', $idcursomodificando, $namesections);
                $table = local_sections::createtable2col($cronograma, 'Semanas', 'Fechas');
                $objetivo = "";
                $sectionnumber = 1; //La seccion en la que debe estar el elemento del cronograma
                $omitirhtml = 0; $sumary = 1;
                local_sections::crearSimpleLabel($idcursomodificando, $sectionnumber, $texto, $omitirhtml, $objetivo,$sumary, $table);
            }/*else{
                    $sectionnumber = 1; //La seccion en la que debe estar el elemento del cronograma
                    $texto = "La iteraccion fue".$iteracion;
                    local_sections::crearSimpleLabel($idcursomodificando, $sectionnumber, $texto);
                }*/
        }
        //FIN
        return null;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function update_sections_returns() {
        return null;
    }



    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function update_sections_sequence_parameters() {
        return new external_function_parameters(
            array(
                'sections' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            //'activityid' => new external_value(
                            //    PARAM_ALPHANUM, 'The ID of the activity or "course" for the course grade item'),
                            'id'  => new external_value(PARAM_INT, 'Id de la seccion'),
                            'course'  => new external_value(PARAM_INT, 'Id del curso al que pertenece la seccion'),
                            'name' => new external_value(PARAM_TEXT, 'El nombre de la seccion'),
                            'summary' => new external_value(PARAM_RAW, 'HTML del resumen de la seccion',VALUE_DEFAULT,
                                '', NULL_NOT_ALLOWED),
                            'summaryformat' => new external_value(PARAM_INT, 'Formato del sumario',VALUE_DEFAULT,1,NULL_NOT_ALLOWED),
                            'availability' => new external_value(PARAM_ALPHANUM, 'Disponibilidad',VALUE_OPTIONAL,
                                '', NULL_NOT_ALLOWED),
                            'usedefaultname' => new external_value(PARAM_INT, 'Utilizar el valor por default para el nombre de la seccion',VALUE_OPTIONAL,
                                '', NULL_NOT_ALLOWED),
                            //'availableinfo' => new external_value(PARAM_ALPHANUM, 'Info de disponibilidad'),
                            'visible' => new external_value(PARAM_INT, 'Si esta o no visible para los usuarios',VALUE_DEFAULT,1,NULL_NOT_ALLOWED),
                            'sequence' => new external_value(PARAM_TEXT, 'Secuencia dentro del curso',VALUE_OPTIONAL,
                                '', NULL_NOT_ALLOWED),
                        )
                    )
                )
            )
        );

    }

    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function update_sections_sequence($sections) {
        global $CFG, $USER, $DB;
        $params = self::validate_parameters(self::update_sections_sequence_parameters(),
            array('sections' => $sections));
        foreach ($params['sections'] as $index => $section){
            $data = ((object)$section);
            //print_r($data->course);exit;
            $coursecontext = context_course::instance($data->course);

            // --- aqui va ---
            $sectioninfo = get_fast_modinfo($data->course)->get_section_info($data->id);

            $initialdata = convert_to_array($sectioninfo);

            //-- aqiu va
            //$data->section = $data->id;

            $section = $DB->get_record('course_sections', array('id' => $initialdata['id']), '*', MUST_EXIST);
            $section->name = $data->name;
            //$section->summary = '<div style="background: #F3FAFF; padding:30px;">'.$data->summary.'</div>';

            $texto = "Objetivo General";
            $section->summary = '<div style="background: #F3FAFF; padding:30px;">'.'<h3 style="color:#296ab0; margin-bottom: 30px; text-shadow: rgba(0, 0, 0, 0.1) 2px 2px 2px;text-align:center;font-weight: 600;">'.$texto.'</h3>'.'<p align="center">'.$data->summary.'</p></div>';
            if($section->section == 1){
                $data->name = 'Planificación';
                $data->summary = '';
            }

            //$section->summary = $data->summary; //LFAS 12/12/2023
            $section->visible = 1;
            //print_object($section);exit;
            $data = $section;
            //$sectionnum = $section->section;

            try {
                self::validate_context($coursecontext);
            } catch (Exception $e) {
                $exceptionparam = new stdClass();
                $exceptionparam->message = $e->getMessage();
                $exceptionparam->courseid = $params['courseid'];
                throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);
            }

            require_capability('moodle/course:update', $coursecontext);
            //print_object($data);exit;
            $course = $DB->get_record('course', array('id' => $data->course), '*', MUST_EXIST);
            if (!empty($CFG->enableavailability)) {
                // Renamed field.
                $data->availability = $data->availabilityconditionsjson;
                unset($data->availabilityconditionsjson);
                if ($data->availability === '') {
                    $data->availability = null;
                }
            }
            $data->availability = '{"op":"&","c":[],"showc":[]}';

            $DB->update_record('course_sections', $data);
            rebuild_course_cache($course->id, true);

            // ---------- Dejar visible o no la sección ---------------
            if (has_capability('moodle/course:sectionvisibility', $coursecontext)) {
                if (!$data->visible) {
                    set_section_visible($course->id, $hide, '0');
                }else if ($data->visible) {
                    set_section_visible($course->id, $show, '1');
                }
            }
            // ---------------------------------------------------------


            if (isset($data->section)) {
                // Usually edit form does not change relative section number but just in case.
                $sectionnum = $data->section;
            }
            course_get_format($course->id)->update_section_format_options($data);

            // Set section info, as this might not be present in form_data.
            if (!isset($data->section))  {
                $data->section = $sectionnum;
            }
            // Trigger an event for course section update.
            $event = \core\event\course_section_updated::create(
                array(
                    'objectid' => $data->id,
                    'courseid' => $course->id,
                    'context' => $coursecontext,
                    'other' => array('sectionnum' => $data->section)
                )
            );
            $event->trigger();


        }
        return true;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function update_sections_sequence_returns() {
        return new external_value(PARAM_BOOL, 'True si fueron actualizadas correctamente');
    }


    //Modificaciones CTIE 23/11/2023 LFAS
    /**
     * Returns array of dates with separator for function createtable2col
     *
     * @return array
     * @since Moodle 4.0
     */
    public static function dividirFechas($fechaInicio='', $fechaFin='',$courseid, $namesections)
    {
        //fechas deben de ser enviadas en formato timestap
        // Establece la configuración local a español
        setlocale(LC_TIME, 'es_ES.UTF-8');
        global  $DB;
        $course = get_course($courseid);
        $numsections = "SELECT  * FROM mdl_course_sections WHERE course = " . $course->id;
        $sectionsobject = $DB->get_records_sql($numsections);
        $numsectionsobject = count($sectionsobject);
        $fechaInicio = $course->startdate;
        $fechaFinTimestamp = $course->enddate;

        // Convierte las cadenas de fecha en objetos DateTime
        $inicio = new DateTime();
        $inicio->setTimestamp($fechaInicio);

        if($fechaFinTimestamp == 0)
        {
            //calcula la fecha de fin a partir de las secciones menos dos por la sección general y la informativa
            $fechaFinTimestamp = $fechaInicio+(($numsectionsobject-2)*7*24*60*60)-(24*60*60);
            $fin = new DateTime();
            $fin->setTimestamp($fechaFinTimestamp);
        }else{
            $fin = new DateTime();
            $fin->setTimestamp($fechaFin);
        }
        /*$namessections = array('Section0');
        foreach ($sectionsobject as $secciones){
            if($secciones->name == $areglonames[0])
            {
                continue;
            }if( $secciones->name == $areglonames[1] )
            {
                continue;
            }
            else{
                array_push($namessections,$secciones->name);
            }
        }*/
        //comienza en dos para saltar las primeras dos secciones
        $i=1;
        // Itera mientras la fecha de inicio sea menor o igual a la fecha de fin
        while ($inicio <= $fin) {
            // Crea formateadores de fecha para la fecha de inicio y la fecha después de 7 días
            $inicioFormatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE d \'de\' MMMM \'de\' y');
            $finFormatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE d \'de\' MMMM \'de\' y');
            // Añade 7 días a la fecha de inicio

            $dateinico = $inicioFormatter->format($inicio);
            // Añade 7 días a la fecha de inicio
            $inicio->add(new DateInterval('P6D'));

            $dateend = $finFormatter->format($inicio);
            // Imprime la fecha después de 7 días
            $arraysemana .= $namesections[$i]."|".$dateinico  ." al ". $dateend."/";
            $inicio->add(new DateInterval('P1D'));
            $i++;
        }
        return $arraysemana;

    }

    /**
     * Returns on format table HTML
     *
     * @return string
     * @since Moodle 4.0
     */
    public static function createtable2col($data,$col1,$col2){

        function convertDate($fecha_str,$dias=0)
        {
// Eliminar las palabras "de" para crear un formato válido para PHP
            $fecha_str = str_replace(' de ', ' ', $fecha_str);

            // Mapeamos los meses en español a inglés para que PHP los reconozca
            $meses_espanol_a_ingles = [
                'enero' => 'January',
                'febrero' => 'February',
                'marzo' => 'March',
                'abril' => 'April',
                'mayo' => 'May',
                'junio' => 'June',
                'julio' => 'July',
                'agosto' => 'August',
                'septiembre' => 'September',
                'octubre' => 'October',
                'noviembre' => 'November',
                'diciembre' => 'December'
            ];

            // Reemplazamos el mes en español por el equivalente en inglés
            foreach ($meses_espanol_a_ingles as $mes_espanol => $mes_ingles) {
                if (strpos($fecha_str, $mes_espanol) !== false) {
                    $fecha_str = str_replace($mes_espanol, $mes_ingles, $fecha_str);
                    break;  // Solo se reemplaza el primer mes encontrado
                }
            }

            // Convertir la fecha a un formato que PHP pueda manejar (como '2025-06-22')
            $fecha = DateTime::createFromFormat('d F Y', $fecha_str);

            // Verificar si la fecha se creó correctamente
            if ($fecha) {
                // Restar un día
                if($dias != 0){
                    $dias = '-'.$dias.' day';
                }
                $fecha->modify($dias);

                // Mapeamos de vuelta el mes en inglés al mes en español
                $meses_ingles_a_espanol = array_flip($meses_espanol_a_ingles);

                // Obtener el nombre del mes en español
                $mes_en_espanol = $meses_ingles_a_espanol[$fecha->format('F')];

                // Mostrar la nueva fecha en formato "21 de junio de 2025"
                return $fecha->format('d') . ' de ' . $mes_en_espanol . ' de ' . $fecha->format('Y');  // Resultado esperado: '21 de junio de 2025'

            }
        }


        $descripcion = '';
        $sepratedata = explode('/',$data);
        $arraysemana = [];

        if(sizeof($sepratedata) > 1){
            //verifica que tenga descripción
            $datatable = explode('|',$sepratedata[0]);
            $i=0;
            if(sizeof($datatable) == 1){
                //es descripción
                $descripcion = $datatable[0];
                if($descripcion){
                    $i=1;
                    $descripcion = "<p>".$descripcion."</p>";
                }
            }
            //Verifica la infroamción apra la tabla
            $htmltable = '';
            for($i; $i< sizeof($sepratedata); $i++){
//            print_object($sepratedata[$i]);
                $datatable = explode('|',$sepratedata[$i]);
//            print_object($datatable);
                if(sizeof($datatable)==1){
                    $htmltable .= "<tr><th colspan='2'>$datatable[0]</th></tr>";
                }else{
                    $arraysemana[$i] = $datatable[1] ;
                    $htmltable .= '<tr>
                <td>'.$datatable[0].'</td>
                <td>'.$datatable[1].'</td>
            </tr>';
                }


            }

        }else{
            $htmltable = $data;
        }

        $extrainfo ='';
        preg_match('/al\s+\w+\s+(\d+\s+de\s+\w+\s+de\s+\d{4})/', end($arraysemana), $matches);

        if (isset($matches[1])) {
            $fechaatencion = $matches[1];
            $dias =1;
            $fechasolic = convertDate($fechaatencion,$dias);
            $dias =2;
            $fechaenviocal = convertDate($fechaatencion,$dias);
            $dias =4;
            $fechacierre = convertDate($fechaatencion,$dias).', 23:55 horas';
            $extrainfo = '<tr>'.
                '<th colspan="2"><span><strong>Cierre de actividades en plataforma: </strong>'.$fechacierre.'<br>'.
                '<strong>Envío de calificaciones finales: </strong>'.$fechaenviocal.'<br>'.
                '<strong>Solicitud de aclaración de calificaciones: </strong>'.$fechasolic.'<br>'.
                '<strong>Atención de aclaración de calificaciones: </strong>'.$fechaatencion.'<br>'.
                '</span></tr>';
        }

        if($matches != ''){
            $htmltable = $htmltable.$extrainfo;
        }

        $tabla = '
            '.$descripcion.'
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">'.$col1.'</th>
                    <th scope="col">'.$col2.'</th>
                </tr>
                </thead>
                <tbody>
                '.$htmltable.'
                </tbody>
            </table>';
        return $tabla;
    }


    public static function crearSimpleLabel($courseid, $sectionnumber, $texto, $omitirhtml = 0, $descripcion = "",$sumary = 0,$tabla = ''){

        global $CFG, $DB;
        require_once("$CFG->dirroot/course/modlib.php");

        // Verifica existencia del curso
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $add = 'label';

        // Generar contenido de la etiqueta
        if ($omitirhtml == 0) {
            $contenido = '';
            $extrainfo = '';
            if($tabla != ''){
                $contenido .= '<div style="background: #FBDFEA5E;">';
            }
            if($sumary == 1) {
                $contenido .= '<div class="pleca-item">&nbsp;</div>';
            }

            $contenido .= '<div style="padding:30px;">';

            if($sumary == 1) {
                $contenido .= '<h3 style="color:#611232; margin-bottom: 30px; text-shadow: rgba(0, 0, 0, 0.1) 2px 2px 2px; text-align:center; font-weight: 600;">' . $texto . '</h3>';
            }
            if($sumary == 0){
                $extrainfo = '<b>Objetivo específico: </b>';
            }
            if($tabla != ''){
                $contenido .= $tabla;
                $contenido .= '</div>';
            }else{
                $contenido .= ($descripcion ?: $extrainfo.'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam tristique scelerisque sem sit amet tristique. Integer ut tincidunt sapien. Vestibulum ultricies turpis vel ipsum molestie, ac lacinia risus porta. Morbi quis mi malesuada tortor sodales auctor</p>');
                $contenido .= '</div>';
            }
        } else {
            $contenido = '<div style="background: #FBDFEA5E;">' . $texto . '</div>';
        }

        // Asegura que las secciones existen
        course_create_sections_if_missing($course, range(0, $sectionnumber));

        // Verifica permisos y contexto
        list($module, $context, $cw) = can_add_moduleinfo($course, $add, $sectionnumber);

        // Prepara datos para el formulario
        $data = (object)[
            'section'          => $sectionnumber,
            'visible'          => $cw->visible,
            'course'           => $course->id,
            'module'           => $module->id,
            'modulename'       => $module->name,
            'groupmode'        => $course->groupmode,
            'groupingid'       => $course->defaultgroupingid,
            'add'              => $add,
            'return'           => 0,
            'sr'               => 0,
            'name'             => $texto,
            'introeditor'      => ['text' => $contenido, 'format' => 1 , 'itemid'=>-1],
            'mform_isexpanded_id_content' => 1
        ];

        // Carga el formulario del módulo
        $modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
        if (!file_exists($modmoodleform)) {
            print_error('noformdesc');
        }
        require_once($modmoodleform);

        // Crea la etiqueta en el curso
        return add_moduleinfo($data, $course);
    }

    //Fin Modificaciones CTIE 23/11/2023 LFAS




    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.7
     */
    public static function delete_sections_parameters() {
        return new external_function_parameters ( array (
            'courseid' => new external_value ( PARAM_INT, 'id of course' ),
            'sectionid' => new external_value ( PARAM_INT, 'Id de la seccion a obtener' )
        ) );
    }

    /**
     * Esta funcion fue hecha para agregar o quitar secciones en un curso
     *
     * @param  int $courseid        Course id
     * @param  int increase      Indica si incrementa o decrementa las secciones
     * @since Moodle 2.7
     */
    public static function delete_sections($courseid,$sectionid) {
        global $CFG, $USER, $DB;
        $params = self::validate_parameters(self::delete_sections_parameters(),
            array('courseid' => $courseid, 'sectionid' => $sectionid));



        $id = $params['sectionid'];

        //$PAGE->set_url('/course/editsection.php', array('id'=>$id, 'sr'=> $sectionreturn));

        $section = $DB->get_record('course_sections', array('id' => $id), '*', MUST_EXIST);
        if($section->course!=$params['courseid']){//Los datos de la seccion no coinciden con los del curso
            $exceptionparam = new stdClass();
            $exceptionparam->message = "Los identificadores de la sección no corresponden a los identificadores del curso";
            $exceptionparam->courseid = $params['courseid'];
            $exceptionparam->sectionid = $params['sectionid'];
            throw new moodle_exception('errorcoursesectionnotvalid' , 'webservice', '', $exceptionparam);
        }
        $course = $DB->get_record('course', array('id' => $section->course), '*', MUST_EXIST);
        $sectionnum = $section->section;

        //require_login($course);
        $context = context_course::instance($course->id);
        require_capability('moodle/course:update', $context);

        // Get section_info object with all availability options.
        $sectioninfo = get_fast_modinfo($course)->get_section_info($sectionnum);

        // Deleting the section.
        //if ($deletesection) {
        //$cancelurl = course_get_url($course, $sectioninfo, array('sr' => $sectionreturn));
        if (course_can_delete_section($course, $sectioninfo)) {
            course_delete_section($course, $sectioninfo, true);
        } else {
            $exceptionparam = new stdClass();
            $exceptionparam->message = get_string('nopermissions')." - ".get_string('deletesection');
            $exceptionparam->courseid = $params['courseid'];
            $exceptionparam->sectionid = $params['sectionid'];
            throw new moodle_exception('errorcoursecontextnotvalid' , 'webservice', '', $exceptionparam);

        }
        //}

        return null;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.7
     */
    public static function delete_sections_returns() {
        return null;
    }



}
