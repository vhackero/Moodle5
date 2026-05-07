<?php

/**
 * Clase que registra los servicios relacionados a los cursos
 *
 * @package    local
 * @author macuco juan.manuel.mp8@gmail.com
 * @since Moodle 2.7
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
// echo $CFG->dirroot.'/course/lib.php';
require_once ("$CFG->libdir/externallib.php");
 require_once($CFG->dirroot.'/course/lib.php');
/**
 * core grades functions
 */


/**
 * NOTAS IMPORTANTES PARA AULA MODELO
 * Instalación de Big Blue Button
 * Instalación de Format_buttons
 */
class local_courses extends external_api {
	
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 * @since Moodle 2.3
	 */
	public static function get_courses_without_idnumber_parameters() {
		return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'id of course',VALUE_DEFAULT,null)
            )
        );
	}
	
	/**
	 * Get courses
	 *
	 * @param array $options
	 *        	It contains an array (list of ids)
	 * @return array
	 * @since Moodle 2.2
	 */
	public static function get_courses_without_idnumber() {
		global $CFG, $DB;
		require_once ($CFG->dirroot . "/course/lib.php");
		
		// validate parameter
		/*$params = self::validate_parameters ( self::get_courses_parameters (), array (
				'options' => $options 
		) );*/
		
		// retrieve courses
		//if (! array_key_exists ( 'ids', $params ['options'] ) or empty ( $params ['options'] ['ids'] )) {
			$courses = $DB->get_records ( 'course',array("idnumber"=>'') );
		//} else {
			//$courses = $DB->get_records_list ( 'course', 'id', $params ['options'] ['ids'] );
		//}
		
		// create return value
		$coursesinfo = array ();
		foreach ( $courses as $course ) {
			
			// now security checks
			$context = context_course::instance ( $course->id, IGNORE_MISSING );
			$courseformatoptions = course_get_format ( $course )->get_format_options ();
			try {
				self::validate_context ( $context );
			} catch ( Exception $e ) {
				$exceptionparam = new stdClass ();
				$exceptionparam->message = $e->getMessage ();
				$exceptionparam->courseid = $course->id;
				throw new moodle_exception ( 'errorcoursecontextnotvalid', 'webservice', '', $exceptionparam );
			}
			require_capability ( 'moodle/course:view', $context );
			
			$courseinfo = array ();
			$courseinfo ['id'] = $course->id;
			$courseinfo ['fullname'] = $course->fullname;
			$courseinfo ['shortname'] = $course->shortname;
			$courseinfo ['categoryid'] = $course->category;
			list ( $courseinfo ['summary'], $courseinfo ['summaryformat'] ) = external_format_text ( $course->summary, $course->summaryformat, $context->id, 'course', 'summary', 0 );
			$courseinfo ['format'] = $course->format;
			$courseinfo ['startdate'] = $course->startdate;
			if (array_key_exists ( 'numsections', $courseformatoptions )) {
				// For backward-compartibility
				$courseinfo ['numsections'] = $courseformatoptions ['numsections'];
			}
			
			// some field should be returned only if the user has update permission
			$courseadmin = has_capability ( 'moodle/course:update', $context );
			if ($courseadmin) {
				$courseinfo ['categorysortorder'] = $course->sortorder;
				$courseinfo ['idnumber'] = $course->idnumber;
				$courseinfo ['showgrades'] = $course->showgrades;
				$courseinfo ['showreports'] = $course->showreports;
				$courseinfo ['newsitems'] = $course->newsitems;
				$courseinfo ['visible'] = $course->visible;
				$courseinfo ['maxbytes'] = $course->maxbytes;
				if (array_key_exists ( 'hiddensections', $courseformatoptions )) {
					// For backward-compartibility
					$courseinfo ['hiddensections'] = $courseformatoptions ['hiddensections'];
				}
				$courseinfo ['groupmode'] = $course->groupmode;
				$courseinfo ['groupmodeforce'] = $course->groupmodeforce;
				$courseinfo ['defaultgroupingid'] = $course->defaultgroupingid;
				$courseinfo ['lang'] = $course->lang;
				$courseinfo ['timecreated'] = $course->timecreated;
				$courseinfo ['timemodified'] = $course->timemodified;
				$courseinfo ['forcetheme'] = $course->theme;
				$courseinfo ['enablecompletion'] = $course->enablecompletion;
				$courseinfo ['completionnotify'] = $course->completionnotify;
				$courseinfo ['courseformatoptions'] = array ();
				foreach ( $courseformatoptions as $key => $value ) {
					$courseinfo ['courseformatoptions'] [] = array (
							'name' => $key,
							'value' => $value 
					);
				}
			} 
			
			if ($courseadmin or $course->visible or has_capability ( 'moodle/course:viewhiddencourses', $context )) {
				$coursesinfo [] = $courseinfo;
			}
		}
		
		return $coursesinfo;
	}
	
	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 * @since Moodle 2.2
	 */
	public static function get_courses_without_idnumber_returns() {
		return new external_multiple_structure ( new external_single_structure ( array (
				'id' => new external_value ( PARAM_INT, 'course id' ),
				'shortname' => new external_value ( PARAM_TEXT, 'course short name' ),
				'categoryid' => new external_value ( PARAM_INT, 'category id' ),
				'categorysortorder' => new external_value ( PARAM_INT, 'sort order into the category', VALUE_OPTIONAL ),
				'fullname' => new external_value ( PARAM_TEXT, 'full name' ),
				'idnumber' => new external_value ( PARAM_RAW, 'id number', VALUE_OPTIONAL ),
				'summary' => new external_value ( PARAM_RAW, 'summary' ),
				'summaryformat' => new external_format_value ( 'summary' ),
				'format' => new external_value ( PARAM_PLUGIN, 'course format: weeks, topics, social, site,..' ),
				'showgrades' => new external_value ( PARAM_INT, '1 if grades are shown, otherwise 0', VALUE_OPTIONAL ),
				'newsitems' => new external_value ( PARAM_INT, 'number of recent items appearing on the course page', VALUE_OPTIONAL ),
				'startdate' => new external_value ( PARAM_INT, 'timestamp when the course start' ),
				'numsections' => new external_value ( PARAM_INT, '(deprecated, use courseformatoptions) number of weeks/topics', VALUE_OPTIONAL ),
				'maxbytes' => new external_value ( PARAM_INT, 'largest size of file that can be uploaded into the course', VALUE_OPTIONAL ),
				'showreports' => new external_value ( PARAM_INT, 'are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL ),
				'visible' => new external_value ( PARAM_INT, '1: available to student, 0:not available', VALUE_OPTIONAL ),
				'hiddensections' => new external_value ( PARAM_INT, '(deprecated, use courseformatoptions) How the hidden sections in the course are displayed to students', VALUE_OPTIONAL ),
				'groupmode' => new external_value ( PARAM_INT, 'no group, separate, visible', VALUE_OPTIONAL ),
				'groupmodeforce' => new external_value ( PARAM_INT, '1: yes, 0: no', VALUE_OPTIONAL ),
				'defaultgroupingid' => new external_value ( PARAM_INT, 'default grouping id', VALUE_OPTIONAL ),
				'timecreated' => new external_value ( PARAM_INT, 'timestamp when the course have been created', VALUE_OPTIONAL ),
				'timemodified' => new external_value ( PARAM_INT, 'timestamp when the course have been modified', VALUE_OPTIONAL ),
				'enablecompletion' => new external_value ( PARAM_INT, 'Enabled, control via completion and activity settings. Disbaled,
                                        not shown in activity settings.', VALUE_OPTIONAL ),
				'completionnotify' => new external_value ( PARAM_INT, '1: yes 0: no', VALUE_OPTIONAL ),
				'lang' => new external_value ( PARAM_SAFEDIR, 'forced course language', VALUE_OPTIONAL ),
				'forcetheme' => new external_value ( PARAM_PLUGIN, 'name of the force theme', VALUE_OPTIONAL ),
				'courseformatoptions' => new external_multiple_structure ( new external_single_structure ( array (
						'name' => new external_value ( PARAM_ALPHANUMEXT, 'course format option name' ),
						'value' => new external_value ( PARAM_RAW, 'course format option value' ) 
				) ), 'additional options for particular course format', VALUE_OPTIONAL ) 
		), 'course' ) );
	}
	
	
	
	/**
	 * Returns description of method parameters
	 *
	 * @return external_function_parameters
	 * @since Moodle 2.2
	 */
	public static function create_course_notvisible_parameters() {
		$courseconfig = get_config('moodlecourse'); //needed for many default values
		return new external_function_parameters(
				array(
						'course' => new external_multiple_structure(
								new external_single_structure(
										array(
                                            'categoryid' => new external_value(PARAM_INT, 'category id'),
                                            'fullname' => new external_value(PARAM_TEXT, 'full name'),
                                            'shortname' => new external_value(PARAM_TEXT, 'course short name'),
                                            'idnumber' => new external_value(PARAM_RAW, 'id number', VALUE_OPTIONAL),
											'summary' => new external_value(PARAM_RAW, 'summary', VALUE_OPTIONAL),
											'summaryformat' => new external_format_value('summary', VALUE_DEFAULT),
											'format' => new external_value(PARAM_PLUGIN,
														'course format: weeks, topics, social, site,..',
														VALUE_DEFAULT, $courseconfig->format),
                                            'showgrades' => new external_value(PARAM_INT,
														'1 if grades are shown, otherwise 0', VALUE_DEFAULT,
														$courseconfig->showgrades),
                                            'newsitems' => new external_value(PARAM_INT,
														'number of recent items appearing on the course page',
														VALUE_DEFAULT, $courseconfig->newsitems),
                                            'startdate' => new external_value(PARAM_INT,
														'timestamp when the course start', VALUE_OPTIONAL),
                                            'numsections' => new external_value(PARAM_INT,
														'(deprecated, use courseformatoptions) number of weeks/topics',
														VALUE_OPTIONAL),
                                            'maxbytes' => new external_value(PARAM_INT,
														'largest size of file that can be uploaded into the course',
														VALUE_DEFAULT, $courseconfig->maxbytes),
                                            'showreports' => new external_value(PARAM_INT,
														'are activity report shown (yes = 1, no =0)', VALUE_DEFAULT,
														$courseconfig->showreports),
                                            'visible' => new external_value(PARAM_INT,
														'1: available to student, 0:not available', VALUE_OPTIONAL),
                                            'hiddensections' => new external_value(PARAM_INT,
														'(deprecated, use courseformatoptions) How the hidden sections in the course are displayed to students',
														VALUE_OPTIONAL),
												'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible',
														VALUE_DEFAULT, $courseconfig->groupmode),
												'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no',
														VALUE_DEFAULT, $courseconfig->groupmodeforce),
												'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id',
														VALUE_DEFAULT, 0),
												'enablecompletion' => new external_value(PARAM_INT,
														'Enabled, control via completion and activity settings. Disabled,
                                        not shown in activity settings.',
														VALUE_OPTIONAL),
												'completionnotify' => new external_value(PARAM_INT,
														'1: yes 0: no', VALUE_OPTIONAL),
												'lang' => new external_value(PARAM_SAFEDIR,
														'forced course language', VALUE_OPTIONAL),
												'forcetheme' => new external_value(PARAM_PLUGIN,
														'name of the force theme', VALUE_OPTIONAL),
												'courseformatoptions' => new external_multiple_structure(
														new external_single_structure(
																array('name' => new external_value(PARAM_ALPHANUMEXT, 'course format option name'),
																		'value' => new external_value(PARAM_RAW, 'course format option value')
																)),
														'additional options for particular course format', VALUE_OPTIONAL),
										)
										), 'courses to create'
								)
				)
				);
		
	}
	
	/**
	 * Create  courses
	 *
	 * @param array $courses
	 * @return array courses (id and shortname only)
	 * @since Moodle 2.2
	 */
	public static function create_course_notvisible($courses) {


		global $CFG, $DB;
		require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->libdir . '/completionlib.php');
		require_once($CFG->dirroot . "/servicios_web/forum/externallib.php");

//		print_r($_POST);
//		print_r($_GET);
		//$params = self::validate_parameters(self::create_courses_parameters(),
		//		array('course' => $course));
		
		$availablethemes = core_component::get_plugin_list('theme');
		$availablelangs = get_string_manager()->get_list_of_translations();
	
		$transaction = $DB->start_delegated_transaction();

		//$course = $params['courses'][0];

		foreach ($courses as $course) {
            $nametocreatecourse = $course['fullname']; //LFAS  20/12/2023 Para poder consultar el curso del lado del SIGIE y obtener la infromación del plan de estudios
            if(isset($course['numsections']) && $course['numsections']<=0 ){
				unset($course['numsections']);
			}


            //CTIE 22-05-2025 Modificación temporal para no crear los cursos con el numero de secciones de  10 semanas
            if(isset($course['numsections']) && $course['numsections'] != 11){
                $course['numsections'] = 11;
            }

			// Ensure the current user is allowed to run this function
			$context = context_coursecat::instance($course['categoryid'], IGNORE_MISSING);
			try {
				self::validate_context($context);
			} catch (Exception $e) {
				$exceptionparam = new stdClass();
				$exceptionparam->message = $e->getMessage();
				$exceptionparam->catid = $course['categoryid'];
				throw new moodle_exception('errorcatcontextnotvalid', 'webservice', '', $exceptionparam);
			}
			require_capability('moodle/course:create', $context);

			// Make sure lang is valid
			if (array_key_exists('lang', $course) and empty($availablelangs[$course['lang']])) {
				throw new moodle_exception('errorinvalidparam', 'webservice', '', 'lang');
			}
	
			// Make sure theme is valid
			if (array_key_exists('forcetheme', $course)) {
				if (!empty($CFG->allowcoursethemes)) {
					if (empty($availablethemes[$course['forcetheme']])) {
						throw new moodle_exception('errorinvalidparam', 'webservice', '', 'forcetheme');
					} else {
						$course['theme'] = $course['forcetheme'];
					}
				}
			}
	
			//force visibility if ws user doesn't have the permission to set it
			$category = $DB->get_record('course_categories', array('id' => $course['categoryid']));
			if (!has_capability('moodle/course:visibility', $context)) {
				$course['visible'] = $category->visible;
			}
			$course['visible'] = 0; //Forsar que sea invisible
	
			//set default value for completion
			$courseconfig = get_config('moodlecourse');
			if (completion_info::is_enabled_for_site()) {
				if (!array_key_exists('enablecompletion', $course)) {
					$course['enablecompletion'] = $courseconfig->enablecompletion;
				}
			} else {
				$course['enablecompletion'] = 0;
			}
	
			$course['category'] = $course['categoryid'];

			// Summary format.
			$course['summaryformat'] = external_validate_format($course['summaryformat']);
			if (!empty($course['courseformatoptions'])) {
				foreach ($course['courseformatoptions'] as $option) {

					$course[$option['name']] = $option['value'];
				}
			}

            //CTIE 22-05-2025 Modifica la descripción del curso temporal para homologación con otros periodos
            $course['summary'] ='';

			//Note: create_course() core function check shortname, idnumber, category

            $course['id'] = create_course((object) $course)->id;
			$resultcourses[] = array('id' => $course['id'], 'shortname' => $course['shortname']);
		}
		local_forum_external::default_forum($course['id']);

        //Comentado para el aula por defecto del aula modelo
//		local_courses::crearURL($course['id']);
//		local_courses::crearArchivo($course['id']);
        //CETIE LFAS 7/12/2023 Actividades o recursos requeridos para el aula modelo
        $sectionnumber = 0;
        $titulo1 = 'Presentación';
        local_courses::cambiarNombreSeccion($course['id'],$sectionnumber,$titulo1);
        $texto = 'Bienvenida';
        $omitirhtml = 0;
        $summary = 1;
        local_courses::crearSimpleLabel($course['id'],$sectionnumber,$texto,$omitirhtml,"",$summary);
        $texto = 'Presentación del docente';
        local_courses::crearSimpleLabel($course['id'],$sectionnumber,$texto,$omitirhtml,"",$summary);

        $sectionnumber = 1;
        $titulo1 = 'Planificación';
        local_courses::cambiarNombreSeccion($course['id'],$sectionnumber,$titulo1);

        //Válida que existan los datos en el SIGIE [revisar que los datos con BD sean Correctos]
        $introduccion = '';
        $objetivo = '';
        $formaevaluacionfinal = '';
        $cronograma = '';
        $table = '';

        /*$datasigie = local_courses::obtenerinformacionPlan($course['id'],$nametocreatecourse);
        if(sizeof($datasigie) > 0){
            foreach ( $datasigie as $dato){
                $introduccion = $dato['presentacion'];
                $objetivo = $dato['objetivos_generales'];
                $formaevaluacionfinal = $dato['metodologia'];
                $cronograma = "SI";
            }
        }*/


        $texto = 'Introducción a la unidad didáctica';
        $omitirhtml = 0;$summary = 1;
        local_courses::crearSimpleLabel($course['id'],$sectionnumber,$texto,$omitirhtml,$introduccion,$summary);
        $texto = 'Objetivo';
        local_courses::crearSimpleLabel($course['id'],$sectionnumber,$texto,$omitirhtml,$objetivo,$summary);
        /*$texto = 'Competencias a desarrollar';
        local_courses::crearSimpleLabel($course['id'],$sectionnumber,$texto);*/
        $texto = 'Forma de evaluación final';
        /*$table = local_courses::createtable2col($formaevaluacionfinal,'Nombre','Porcentaje');
        $objetivo = "";*/
        $objetivo = $formaevaluacionfinal;
        local_courses::crearSimpleLabel($course['id'],$sectionnumber,$texto, $omitirhtml,"",$summary);
       /* $texto = 'Cronograma';
        if($cronograma == 'SI'){
            $areglonames = array('Presentación','Planificación'); // Para las fechas
            $cronograma = local_courses::dividirFechas('','',$course['id'],$areglonames);
            $table = local_courses::createtable2col($cronograma,'Semanas','Fechas');
        }
        $objetivo = "";
        local_courses::crearSimpleLabel($course['id'],$sectionnumber,$texto,$objetivo,$table);*/
        local_courses::crearSesionBBB($course['id']);
        local_courses::crearForo($course['id']);
//        local_courses::createBlockByName($course['id'],'accessibility');
        local_courses::createBlockByName($course['id'],'completion_progress');
        local_courses::createBlockByName($course['id'],'cien_tecnicas');
        local_courses::createBlockByName($course['id'],'calendar_month');
        local_courses::createBlockByName($course['id'],'exaport');
//        local_courses::createBlockByName($course['id'],'recent_activity');


        //CTIE LFAS 7/12/2023 Creación de etiquetas para descripción del curso
		$transaction->allow_commit();
		return $course['id'];
	}

    //CETIE LFAS 7/12/2023 Actividades o recursos requeridos para el aula modelo
    public static function createBlockByName($courseid, $nameblock){

        global $DB;
        $blockName = $nameblock; // Nombre del bloque

        // Cargar el curso
        $course = get_course($courseid);
        $context = context_course::instance($course->id);
        // Obtener el ID del contexto principal (parentcontextid)
        $parentcontextid = $context->id;
        //valida que exista el bloque en moodle
        if($DB->get_record('block', array('name'=>$nameblock))){
            // Verificar si el bloque ya está agregado al curso
            if (!$DB->record_exists('block_instances', array('blockname' => $blockName, 'parentcontextid' => $parentcontextid))) {
                // Obtener el ID del bloque
                $blockId = $DB->get_field('block', 'id', array('name' => $blockName));

                // Crear una instancia de bloque
                $blockInstance = new stdClass();
                $blockInstance->blockname = $blockName;
                $blockInstance->parentcontextid = $parentcontextid;
                $blockInstance->showinsubcontexts = 0;
                $blockInstance->pagetypepattern = 'course-view-*';
                $blockInstance->subpagepattern = '';
                $blockInstance->defaultregion = 'side-pre'; // Ajusta la región según tus necesidades
                $blockInstance->defaultweight = '1'; // Ajusta la región según tus necesidades
                $blockInstance->timecreated = time(); // Ajusta la región según tus necesidades
                $blockInstance->timemodified = time(); // Ajusta la región según tus necesidades

                // Insertar la instancia del bloque en la base de datos
                $blockInstanceId = $DB->insert_record('block_instances', $blockInstance);

                // Asociar el bloque a la región del curso
                $DB->insert_record('block_positions', array('blockinstanceid' => $blockInstanceId, 'contextid' => $course->id, 'region' => 'side-pre', 'visible' => 1, 'weight' => 0));
                // Forzar actualización de la vista del curso
                rebuild_course_cache($course->id, true);
                $addbloque = true;
            }else{
                $addbloque = false ;
            }
        }
        else {
//        return "No existe el bloque en moodle";
            $addbloque = false ;
        }
        return $addbloque;
    }

    public static function crearSesionBBB($courseid){

        global $CFG, $DB;
		require_once("$CFG->dirroot/course/modlib.php");
		$course = $courseid;
		$section = 0; //sección en la que se va agregar la sesión de BBB
		$add = "bigbluebuttonbn";

		$course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
		course_create_sections_if_missing($course, range(0, $course->numsections));
		list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);


		$data = new stdClass();
		$data->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
		$data->visible          = $cw->visible;
		$data->course           = $course->id;
		$data->module           = $module->id;
		$data->modulename       = $module->name;
		$data->groupmode        = $course->groupmode;
		$data->groupingid       = $course->defaultgroupingid;
		$data->id               = '';
		$data->instance         = '';
		$data->coursemodule     = '';
		$data->add              = $add;
		$data->return           = 0; //must be false if this is an add, go back to course view on cancel
		$data->sr               = 0;
		$data->name				= 'Sala de Conferencias Web';
		$data->introeditor 		= Array('text' => 'Conferencias Web para el aprendizaje','format'=>1);
		$data->mform_isexpanded_id_content = 1;
		$data->display = 6;
		$data->popupwidth = 620;
		$data->popupheight = 450;
		$data->printintro = 1;
		$data->record = 1;
		$data->wait = 1;
		$data->participants = '[{"selectiontype":"all","selectionid":"all","role":"viewer"},{"selectiontype":"role","selectionid":"10","role":"moderator"},{"selectiontype":"role","selectionid":"12","role":"moderator"},{"selectiontype":"role","selectionid":"13","role":"moderator"},{"selectiontype":"role","selectionid":"28","role":"moderator"},{"selectiontype":"role","selectionid":"9","role":"viewer"}]';


		$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
		if (file_exists($modmoodleform)) {
			require_once($modmoodleform);
		} else {
			print_error('noformdesc');
		}

		$formform = add_moduleinfo($data, $course);

        //CTIE 28-02-2024 LFAS Modificación para restringir permiso a DL,AM,RPE en la actividad
        function removepermisionmodule($coursemodule, $rolname, $capability)
        {
            global $DB;
            $context = context_module::instance($coursemodule);
            $role = $DB->get_record('role', array('shortname' => $rolname), '*', MUST_EXIST);
            if ($role) {
                //Valida si ya existe el registro
                $validateinfo = $DB->get_record('role_capabilities', array(
                        'roleid' => $role->id,
                        'contextid' => $context->id,
                        'capability' => $capability,
                    )
                );
                if(!$validateinfo){
                    //inserta el registro
                    $DB->insert_record('role_capabilities', (object)[
                        'roleid' => $role->id,
                        'contextid' => $context->id,
                        'capability' => $capability,
                        'permission' => CAP_PREVENT,
                        'timemodified' => time(),
                        'modifierid' => 2
                    ]);
                }else{
                    //actualiza el recurso
                    $DB->update_record('role_capabilities', (object)[
                        'id' => $validateinfo->id,
                        'roleid' => $role->id,
                        'contextid' => $context->id,
                        'capability' => $capability,
                        'permission' => CAP_PREVENT,
                        'timemodified' => time(),
                        'modifierid' => 2
                    ]);
                }
            }
        }


        $coursemodule = $formform->coursemodule;
        $rolnames = ['unadm_dl','unadm_am','unadm_rpe'];

        for($i=0; $i<sizeof($rolnames); $i++ ){
            $capability = 'moodle/course:activityvisibility';
            removepermisionmodule($coursemodule, $rolnames[$i], $capability);
            $capability = 'moodle/course:manageactivities';
            removepermisionmodule($coursemodule, $rolnames[$i], $capability);
        }

		return $formform;
	}
    public static function crearForo($courseid){

        global $CFG, $DB;
		require_once("$CFG->dirroot/course/modlib.php");
		$course = $courseid;
		$section = 0; //sección en la que se va agregar la sesión de BBB
		$add = "forum";

		$course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
		course_create_sections_if_missing($course, range(0, $course->numsections));
		list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);


		$data = new stdClass();
		$data->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
		$data->visible          = $cw->visible;
		$data->course           = $course->id;
		$data->module           = $module->id;
		$data->modulename       = $module->name;
		$data->groupmode        = $course->groupmode;
		$data->groupingid       = $course->defaultgroupingid;
		$data->id               = '';
		$data->instance         = '';
		$data->coursemodule     = '';
		$data->add              = $add;
		$data->return           = 0; //must be false if this is an add, go back to course view on cancel
		$data->sr               = 0;
		$data->name				= 'Foro de dudas';
		$data->introeditor 		= Array('text' => '','format'=>1);
		$data->mform_isexpanded_id_content = 1;
		$data->display = 6;
		$data->popupwidth = 620;
		$data->popupheight = 450;
		$data->printintro = 1;


		$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
		if (file_exists($modmoodleform)) {
			require_once($modmoodleform);
		} else {
			print_error('noformdesc');
		}

		$formform = add_moduleinfo($data, $course);
		return $formform;
	}
    public static function obtenerinformacionPlan($courseid,$nombrecourse){

        global $CFG, $DB;

        //conexión con el servidor SIGIE

//        $host = "localhost";   //local
//        $host = "3.17.67.194";  //Pre-productivo
        $host = "172.18.30.113"; //Productivo
        $usuario = "elearning";
        $contrasena = "elearning";
        $base_datos = "des_sisi_gestor";

        /*$host = "172.18.26.113";
               $usuario = "elearning";
               $contrasena = "elearning";
               $base_datos = "des_sisi_gestor";*/

        try {
            // Establecer conexión con la base de datos utilizando PDO
            $DBSIGIE = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8", $usuario, $contrasena);
            // Configurar el modo de error y excepción
            $DBSIGIE->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Ejemplo de consulta preparada

        } catch (PDOException $e) {
            // Manejar errores de conexión
            echo "Error de conexión: " . $e->getMessage();
        }
        $data = 'not_find_data';
        if($DBSIGIE){
            $idcoursemoodle = $courseid;
            //Consulta la información del plan
            $status = 0; //cuando no esta suspendido
            $consulta = $DBSIGIE->prepare("SELECT fdp.nombre_tentativo as nombre_programa , fdp.descripcion, fdp.conocimietos_previos, fdp.presentacion, fdp.propositos, fdp.objetivos_generales, fdp.metodologia, fdp.perfil_ingreso, fdp.perfil_egreso, fdp.calificacion_min_aprobatoria, te.objetivo_general_ec as objetivocurso, te.perfil_ec, te.requisitos_ec FROM tbl_eventos AS te 
            INNER JOIN tbl_ficha_descriptiva_programa  AS fdp ON fdp.id_programa = te.id_programa 
            WHERE te.id_curso_lms_borrador = :cursomoodle OR te.nombre_ec = :namecoursetocreate ");
            $consulta->bindParam(':cursomoodle', $idcoursemoodle, PDO::PARAM_STR);
            $consulta->bindParam(':namecoursetocreate', $nombrecourse, PDO::PARAM_STR);
            $consulta->execute();
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            if(sizeof($resultados) > 0){
                $data = $resultados;
            }
        }
		return $data;

	}
    public static function crearSimpleLabel($courseid, $sectionnumber, $texto, $omitirhtml = 0, $descripcion = "",$sumary = 0){

        global $CFG, $DB;
        require_once("$CFG->dirroot/course/modlib.php");

        // Verifica existencia del curso
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $add = 'label';

        // Generar contenido de la etiqueta
        if ($omitirhtml == 0) {
            $contenido = '';
            $extrainfo = '';
            if($sumary == 1) {
                $contenido = '<div class="pleca-item">&nbsp;</div>';
            }
            $contenido .= '<div style="padding:30px;">';
            if($sumary == 1) {
                $contenido .= '<h3 style="color:#611232; margin-bottom: 30px; text-shadow: rgba(0, 0, 0, 0.1) 2px 2px 2px; text-align:center; font-weight: 600;">' . $texto . '</h3>';
            }
            if($sumary == 0){
                $extrainfo = '<b>Objetivo específico: </b>';
            }
            $contenido .= ($descripcion ?: $extrainfo.'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam tristique scelerisque sem sit amet tristique. Integer ut tincidunt sapien. Vestibulum ultricies turpis vel ipsum molestie, ac lacinia risus porta. Morbi quis mi malesuada tortor sodales auctor</p>');
            $contenido .= '</div>';
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
    public static function cambiarNombreSeccion($courseid,$sectionnumber,$newtitle){
        global $CFG, $DB;
        require_once ("$CFG->dirroot/course/lib.php");
        $courseid = $courseid;
        $seccion = $sectionnumber;
        // Obtener el objeto de la sección que deseas modificar
        $section = $DB->get_record('course_sections', array('course' => $courseid,'section'=>$seccion));
        $idSection = $section->id;
        $seccion = new stdClass();
        $seccion->id = $idSection;
        $seccion->section = $seccion;
        $formform = course_update_section($courseid, $seccion, array('id'=>$idSection, 'name'=>$newtitle));
		return $formform;
	}
    public static function createtable2col($data,$col1,$col2){
        $descripcion = '';
        $sepratedata = explode('/',$data);

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
                    $htmltable .= '<tr>
                <td>'.$datatable[0].'</td>
                <td>'.$datatable[1].'</td>
            </tr>';
                }


            }

        }else{
            $htmltable = $data;
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
    //fechas dividida cada 7 días
    public static function dividirFechas($fechaInicio='', $fechaFin='',$courseid, $areglonames='')
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

        $i=1;

        // Itera mientras la fecha de inicio sea menor o igual a la fecha de fin
        while ($inicio <= $fin) {
            // Crea formateadores de fecha para la fecha de inicio y la fecha después de 7 días
            $inicioFormatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE d \'de\' MMMM \'de\' y');
            $finFormatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE d \'de\' MMMM \'de\' y');
            // Añade 7 días a la fecha de inicio

            $dateinico = $inicioFormatter->format($inicio);
            // Añade 7 días a la fecha de inicio
            $inicio->add(new DateInterval('P7D'));

            $dateend = $finFormatter->format($inicio);
            // Imprime la fecha después de 7 días
            $arraysemana .= $namessections[$i]."|".$dateinico  ." al ". $dateend."/";
            $i++;
        }
        return $arraysemana;

    }


    /*public static function changeStyleSumarySection($courseid,$sectionnumber){

        global $CFG, $DB;
        require_once("$CFG->dirroot/course/lib.php");
        $sectionView = $DB->get_record('course_sections', array('course'=>$courseid,'section'=>$sectionnumber));
        $sumarysection = '<div style="background: #F3FAFF; padding:30px;">'.$sectionView->summary.'</div>';

        $section = new stdClass();
        $section->id = $sectionView->id; // ID de la sección que deseas actualizar
        $section->section = $sectionnumber;
        $section->visible = 1;

        $data = new stdClass();
        $data->summary = $sumarysection; // Nueva descripción que deseas asignar
        $data->availability = '{"op":"&","c":[],"showc":[]}';
        course_update_section($courseid,$section,$data);
        return true;

    }*/

    //FIN Funciones CETIE
	public static function crearURL($courseid){
        global $CFG, $DB;
		require_once("$CFG->dirroot/course/modlib.php");
		$course = $courseid;
		$section = 0;
		$add = "url";

		$course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
		course_create_sections_if_missing($course, range(0, $course->numsections));
		list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);


		$data = new stdClass();
		$data->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
		$data->visible          = $cw->visible;
		$data->course           = $course->id;
		$data->module           = $module->id;
		$data->modulename       = $module->name;
		$data->groupmode        = $course->groupmode;
		$data->groupingid       = $course->defaultgroupingid;
		$data->id               = '';
		$data->instance         = '';
		$data->coursemodule     = '';
		$data->add              = $add;
		$data->return           = 0; //must be false if this is an add, go back to course view on cancel
		$data->sr               = 0;
		$data->name				= 'ENCUESTA';
		$data->externalurl		= 'http://www.google.com';
		$data->introeditor 		= Array('text' => 'Esta es mi descripcion','format'=>1);
		$data->mform_isexpanded_id_content = 1;
		$data->display = 6;
		$data->popupwidth = 620;
		$data->popupheight = 450;
		$data->printintro = 1;


		$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
		if (file_exists($modmoodleform)) {
			require_once($modmoodleform);
		} else {
			print_error('noformdesc');
		}

		$formform = add_moduleinfo($data, $course);
		return $formform;
	}

	public static function crearArchivo($courseId){
        global $CFG, $DB;
		require_once("$CFG->dirroot/course/modlib.php");

        $course = $courseId;
		$section = 0;
		$add = "resource";
		
		$course = $DB->get_record('course', array('id'=>$course), '*', MUST_EXIST);
		course_create_sections_if_missing($course, range(0, $course->numsections));
		list($module, $context, $cw) = can_add_moduleinfo($course, $add, $section);
		
		
		
		$data = new stdClass();
		$data->section          = $section;  // The section number itself - relative!!! (section column in course_sections)
		$data->visible          = $cw->visible;
		$data->course           = $course->id;
		$data->module           = $module->id;
		$data->modulename       = $module->name;
		$data->groupmode        = $course->groupmode;
		$data->groupingid       = $course->defaultgroupingid;
		$data->id               = '';
		$data->instance         = '';
		$data->coursemodule     = '';
		$data->add              = $add;
		$data->return           = 0; //must be false if this is an add, go back to course view on cancel
		$data->sr               = 0;
		$data->name				= 'GUÍA DE USO';
		//$data->externalurl		= 'http://www.google.com';
		$data->introeditor 		= Array('text' => 'Esta es mi descripcion','format'=>1);
		$data->mform_isexpanded_id_content = 1;
		$data->files = 654984610;
		$data->display = 6;
		$data->popupwidth = 620;
		$data->popupheight = 450;
		$data->printintro = 1;
		
		
		$modmoodleform = "$CFG->dirroot/mod/$module->name/mod_form.php";
		if (file_exists($modmoodleform)) {
			require_once($modmoodleform);
		} else {
			print_error('noformdesc');
		}
		
		$fromform = add_moduleinfo($data, $course);
		return $fromform;
	}
	
	/**
	 * Returns description of method result value
	 *
	 * @return external_description
	 * @since Moodle 2.2
	 */
	public static function create_course_notvisible_returns() {
		return new external_value(PARAM_INT, 'course id');
	}
	

	
	public static function courses_create_tags_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'tag' => new external_multiple_structure(
								new external_value(PARAM_TEXT, 'tag name')
								)
						
				)
			);
	}

	public static function courses_create_tags($courseid, $tags) {
		global $CFG, $USER, $DB;
		require_once($CFG->dirroot.'/tag/locallib.php');
		
		$params = self::validate_parameters(self::courses_create_tags_parameters(),
				array('courseid' => $courseid, 'tag' => $tags));
		
		//foreach ($params['tag'] as $index => $){
		//$tags = $tag;
		//$USER = get_admin();
		//Check for a valid array of tags
		if(empty($tags) && count($tags)<=0) {
			return false;
		}
		
		$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
		
		if(!$course) {
			return false;
		}
		
		if(!$context = context_course::instance($course->id)) {
			return false;
		}
		
		//Check for string values in tags
		$validtags = array();
		foreach($tags as $tag) {
			if(is_string($tag))
				$validtags[] = $tag;
		}
		
		//Get all existing tags from course
        // use this form moodle 3.0 $coursetags = tag_get_tags('course', $course->id);
        //Modified For Moodle 4.0 CTIE LFAS 6/12/2023
		/*$coursetags = core_tag_tag::get_item_tags('course',$course->id);
		foreach($coursetags as $course_tag){
			//Delete current tags from course
            //	use this for moodle 3.0 tag_set_delete('course', $course->id, $course_tag->name);
            //Modified For Moodle 4.0 CTIE LFAS 6/12/2023
            core_tag_tag::delete_tags($course_tag->id);
		}
		
		//Insert new tags into course
		// Use this for moodle 3.0 tag_set('course', $course->id, $tags, 'core', $context->id);
        //Modified For Moodle 4.0 CTIE LFAS 6/12/2023
        core_tag_tag::set_item_tags('course',$course->id,'core',$context->id);*/



        //tag_set_delete('course', $course->id, 'tlecuitl');
		//tag_set_add('course', $course->id, 'tlecuitl', 'core', $context->id);
		
		//return tag_get_tags('course', $course->id);
		return true;
	}
	
	
	public static function courses_create_tags_returns() {
		return new external_value(PARAM_INT, 'Si fue creado (1) o no fue creado (0)');
	}
	
	
	
	
	
	
	
	
	
	
	
	
	public static function courses_duplicate_course_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'data' => new external_value(PARAM_INT, 'Indica si se realiza la copia con datos (SI = 1) o sin datos (NO = 0)',
														VALUE_DEFAULT, 0),
						'visible' => new external_value(PARAM_INT, 'Indica si la copia será visible (SI = 1) o no visible (NO = 0)',
								VALUE_DEFAULT, 0),
				)
		);
	}
	
	public static function courses_duplicate_course($courseid, $data, $visible) {
		global $CFG, $USER, $DB;
		require_once($CFG->dirroot . '/course/externallib.php');

		$params = self::validate_parameters(self::courses_duplicate_course_parameters(),
				array('courseid' => $courseid, 'data' => $data, 'visible' => $visible));
		
		// The CLONEing options (these are the defaults).
		if($data) {//Si el curso va con datos de usuarios
			$options = array(
					array ('name' => 'activities', 'value' => 1),
					array ('name' => 'blocks', 'value' => 1),
					array ('name' => 'filters', 'value' => 1),
					array ('name' => 'users', 'value' => 1),
					array ('name' => 'role_assignments', 'value' => 1),
					array ('name' => 'comments', 'value' => 1),
					array ('name' => 'userscompletion', 'value' => 1),
					array ('name' => 'logs', 'value' => 1),
					array ('name' => 'grade_histories', 'value' => 1),
			);
		}else{ // El curso van sin datos de usuarios
			$options = array(
					array ('name' => 'activities', 'value' => 1),
					array ('name' => 'blocks', 'value' => 1),
					array ('name' => 'filters', 'value' => 1),
					array ('name' => 'users', 'value' => 0),
					array ('name' => 'role_assignments', 'value' => 0),
					array ('name' => 'comments', 'value' => 0),
					array ('name' => 'userscompletion', 'value' => 0),
					array ('name' => 'logs', 'value' => 0),
					array ('name' => 'grade_histories', 'value' => 0),
			);
		}
		
		// To simplify the skeleton code, let's run the whole thing as an
		// admin. You probably *don't* want to do this in production code.
		$USER = get_admin();
		
		//Get course info
		if(!$origincourse = $DB->get_record('course', array('id' => $courseid))) {
			return false;
		}
		//print_object($origincourse);
		// Get category ID from the original course
		$newcategoryid = $origincourse->category;
		// Rename the new course
		$newfullname = $origincourse->fullname."_copy".time();
		$newshortname = $origincourse->shortname."_c".time();
		
		//echo "El ID del curso es: ".$courseid."<br>";
		//echo "El nombre del curso es: ".$newfullname."<br>";
		//echo "La clave del curso es: ".$newshortname."<br>";
		//echo "La categoria del curso es: ".$newcategoryid."<br>";
		//echo "La visibilidad del curso es: ".$visible."<br>";
		//echo "Los parametros del curso son: <br>";
		//print_object($options);
		//exit;
		
		try {
			$newcourse = core_course_external::duplicate_course($courseid, $newfullname, $newshortname, $newcategoryid, $visible, $options);
		} catch (exception $e) {
			// Some debugging information to see what went wrong
			print_object($e);
			//var_dump($e);
		}
		
		//echo 'Nuevo curso duplica"'. $newcourse['shortname'] . '" and id "' . $newcourse['id'] . "\"\n";
		return $newcourse['id'];
	}
	
	
	public static function courses_duplicate_course_returns() {
		return new external_value(PARAM_INT, 'ID del nuevo curso clonado');
	}
	
	
	
	public static function courses_backup_course_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'data' => new external_value(PARAM_INT, 'Indica si se realiza el respaldo con datos (SI = 1) o sin datos (NO = 0)',
								VALUE_DEFAULT, 0)
				)
				);
	}
    public static function update_enrolments_course_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'userid' => new external_value(PARAM_INT, 'user id'),
						'suspend' => new external_value(PARAM_INT, 'suspend status'),
				)
				);
	}
	
	public static function courses_backup_course($courseid, $data) {
		global $CFG, $USER, $DB;
        require_once($CFG->dirroot . '/course/externallib.php');

		$params = self::validate_parameters(self::courses_backup_course_parameters(),
				array('courseid' => $courseid, 'data' => $data));
	
		// The CLONEing options (these are the defaults).
		if($data) {//Si el curso va con datos de usuarios
			$options = array(
					array ('name' => 'activities', 'value' => 1),
					array ('name' => 'blocks', 'value' => 1),
					array ('name' => 'filters', 'value' => 1),
					array ('name' => 'users', 'value' => 1),
					array ('name' => 'role_assignments', 'value' => 1),
					array ('name' => 'comments', 'value' => 1),
					array ('name' => 'userscompletion', 'value' => 1),
					array ('name' => 'logs', 'value' => 1),
					array ('name' => 'grade_histories', 'value' => 1),
			);
		}else{ // El curso van sin datos de usuarios
			$options = array(
					array ('name' => 'activities', 'value' => 1),
					array ('name' => 'blocks', 'value' => 1),
					array ('name' => 'filters', 'value' => 1),
					array ('name' => 'users', 'value' => 0),
					array ('name' => 'role_assignments', 'value' => 0),
					array ('name' => 'comments', 'value' => 0),
					array ('name' => 'userscompletion', 'value' => 0),
					array ('name' => 'logs', 'value' => 0),
					array ('name' => 'grade_histories', 'value' => 0),
			);
		}
        return  'https://aula-modelo41.unadmexico.mx/cursoscai.mbz';
		//return 'https://fs01n1.sendspace.com/dl/f3c4fe57fd1052d75f3598339abb489b/59644fef010da9ca/d596a1/respaldo-moodle2-course-12-ec-26-20170710-2223-nu.mbz';
	}
	
	
	public static function courses_backup_course_returns() {
		return new external_value(PARAM_TEXT, 'URL para descargar el archivo de respaldo');
	}

    public static function update_enrolments_course($courseid, $userid, $suspend = 1){
        global $CFG, $DB;
        $params = self::validate_parameters(self::update_enrolments_course_parameters(),
            array('courseid' => $courseid, 'userid' => $userid, 'suspend' => $suspend));
        $enrolid = $DB->get_record('enrol',array('courseid'=>$courseid,'enrol'=>'manual'));
        if($enrolid) {
            $userenrolid = $DB->get_record('user_enrolments', array('enrolid' => $enrolid->id, 'userid' => $userid));
            if($userenrolid) {
                $datasend = new stdClass();
                $datasend->id = $userenrolid->id;
                $datasend->status = $suspend;
                $actualizainfoMoodle = $DB->update_record('user_enrolments', $datasend);
                if ($actualizainfoMoodle) {
                    return $userenrolid->id;
                } else {
                    return 0;
                }
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }
    public static function update_enrolments_course_returns() {
		return new external_value(PARAM_INT, 'Id de enrolamiento al curso en moodle');
	}
	
	
	
	
	
	public static function courses_avance_oas_parameters() {
		return new external_function_parameters(
				array(
						'courseid' => new external_value(PARAM_INT, 'course id'),
						'userid' => new external_value(PARAM_INT, 'user id'),
				)
				);
	}
	
	public static function courses_avance_oas($courseid, $userid) {
		global $CFG, $USER, $DB;
//        require_once($CFG->dirroot.'/block/completion_progress/renderer.php');
        require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
        require_once($CFG->dirroot.'/blocks/completion_progress/block_completion_progress.php');

		$params = self::validate_parameters(self::courses_avance_oas_parameters(),
				array('courseid' => $courseid, 'userid' => $userid ));

        /*--- CALCULAR LAS CALIFICACIONES DE CADA OBJETO DE APRENDISAJE ---------*/
        /*$modinfo = get_fast_modinfo($courseid);
        $course = course_get_format($courseid)->get_course();
        $numSections = 0;
        $calificacionTrofeo = 0;
		$numeroMedallas = 0;
		$calificacionMedalla = array();
		foreach ($modinfo->get_section_info_all() as $section => $thissection) {
			// ID de actividades deseccion: $modinfo->sections[$thissection->section]
			$showsection = $thissection->uservisible ||
			($thissection->visible && !$thissection->available &&
					!empty($thissection->availableinfo));
			if ($thissection->uservisible) {
				if ($section > $course->numsections || $section==0) {
					continue;
				}
			}
			$numSections++;
		
			if(!isset($modinfo->sections[$thissection->section])){
				continue;
			}
		
		
			$totalCalificacion = 0;
			$objetos = 0;
		
			foreach ($modinfo->sections[$thissection->section] as $modnumber) {
				$mod = $modinfo->cms[$modnumber];
				include_once($CFG->dirroot.'/mod/'.$mod->modname.'/locallib.php');
				if(strpos($mod->modname, 'scorm') !== false){
					if (! $cm = get_coursemodule_from_id($mod->modname, $mod->id, 0, true)) {
						print_error('invalidcoursemodule');
					}
					if (! $scorm = $DB->get_record($mod->modname, array("id" => $cm->instance))) {
						print_error('invalidcoursemodule');
					}
		
					if($mod->modname=='scormsisi'){
						$calculatedgrade = scormsisi_grade_user($scorm, $userid);
					}else{
						$calculatedgrade = scorm_grade_user($scorm, $userid);
					}
		
					if ($scorm->grademethod !== GRADESCOES && !empty($scorm->maxgrade)) {
						$calculatedgrade = $calculatedgrade / $scorm->maxgrade;
						//$calculatedgrade = number_format($calculatedgrade * 100, 0) .'%';
					}
		
					$totalCalificacion += ($calculatedgrade*100);
					$objetos++;
				}
			}
			$numeroMedallas ++;
			if($objetos>0){
				//print_object($totalCalificacion/$objetos);
				$calificacionMedalla[] = ($totalCalificacion/$objetos);
				$calificacionTrofeo += ($totalCalificacion/$objetos);
			}else{
				$calificacionMedalla[] = 0;
			}
		}
		
		//$calificacionTrofeo = 0;
		if($numeroMedallas>0){
			$calificacionTrofeo = round($calificacionTrofeo/$numeroMedallas);
		}
		
		$tiposMedallas = array();
		$medallasTerminadas = 0;
		foreach($calificacionMedalla as $i=>$cal){
			$tiposMedallas[] = $cal>=80?'activa':'inactiva';
			$medallasTerminadas += $cal>=80?1:0;
		
		}
		
		//$this->calificacionMedallas = $calificacionMedalla;
		
		$avanceMedallas = 0;
		if($numeroMedallas>0){
			$avanceMedallas = round($medallasTerminadas * 100 / $numeroMedallas);
		}
			
		$respuesta = array();
		$respuesta['trofeo'] = $calificacionTrofeo;
		$respuesta['medallas'] = $calificacionMedalla;
		$respuesta['avanceMedallas'] = $avanceMedallas;
		$respuesta['tiposMedallas'] = $tiposMedallas;
//		return $respuesta['avanceMedallas'];
        */

        //CTIE LFAS 6-2-2024 modificación para mostrar el porcentaje de avance de la asignatura

        try {
            $course = get_course($courseid);
        } catch (Exception $e) {
            // El curso NO existe.
            die();
        }

        $datacourse = new \block_completion_progress\completion_progress($courseid);
        $user = core_user::get_user($userid);
        //instancia de tipo completion_progress
        $sqlrecord = 'SELECT *
        FROM mdl_block_instances
        WHERE parentcontextid IN (
            SELECT id
            FROM mdl_context
            WHERE contextlevel = 50 -- Nivel de contexto para curso
            AND instanceid = (SELECT id FROM mdl_course c WHERE c.id  = '.$courseid.')
        )AND blockname = "completion_progress"';
        $instancia = $DB->get_record_sql($sqlrecord);
        $porcentaje = 0;
        if($instancia) {
            $datacourse->for_user($user);
            $datacourse->for_user($user)->for_block_instance($instancia);
            $datacourse->get_visible_activities();
            $datacourse->get_completions();
            $datacourse->get_block_config();
            $datacourse->get_user()->id;
            $datacourse->get_course()->id;
            $datacourse->get_block_instance()->id;
            $porcentaje = $datacourse->get_percentage();
        }
//        //Valida que no exista una baja
//        function conection_db(){
//            // Configuración de la base de datos
////          $host = "localhost";   //local
//            $host = "3.17.67.194";  //Pre-productivo
////          $host = "172.18.30.113"; //Productivo
//            $usuario = "elearning";
//            $contrasena = "elearning";
//            $base_datos = "des_sisi_gestor";
//            try {
//                // Establecer conexión con la base de datos utilizando PDO
//                $pdo = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8", $usuario, $contrasena);
//                // Configurar el modo de error y excepción
//                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                // Ejemplo de consulta preparada
//
//            } catch (PDOException $e) {
//                // Manejar errores de conexión
//                echo "Error de conexión: " . $e->getMessage();
//            }
//            // Cerrar la conexión al finalizar
//            return $pdo;
//        }
//        function rel_personas_plataformas_moodle($DBSIGIE,$userid){
//            $consulta = $DBSIGIE->prepare("SELECT id_persona FROM rel_personas_plataformas_moodle WHERE id_persona_moodle = :user");
//            $consulta->bindParam(':user', $userid, PDO::PARAM_STR);
//            $consulta->execute();
//            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
//            return $resultados[0]['id_persona'];
//        }
//        function get_id_evento($DBSIGIE,$courseid){
//            $consulta = $DBSIGIE->prepare("SELECT id_evento FROM tbl_eventos WHERE id_curso_lms_borrador = :courseid");
//            $consulta->bindParam(':courseid', $courseid, PDO::PARAM_STR);
//            $consulta->execute();
//            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
//            return $resultados[0]['id_evento'];
//        }
//        function get_motivo_baja_id($DBSIGIE,$idpersona,$idevento){
//            $consulta = $DBSIGIE->prepare("SELECT motivo_baja_id FROM rel_persona_bajas WHERE id_persona = :idpersona AND id_evento = :idevento");
//            $consulta->bindParam(':idpersona', $idpersona, PDO::PARAM_STR);
//            $consulta->bindParam(':idevento', $idevento, PDO::PARAM_STR);
//            $consulta->execute();
//            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
//            return $resultados[0]['motivo_baja_id'];
//        }
//        function get_tipo_baja($DBSIGIE,$motivobajaid){
//            $consulta = $DBSIGIE->prepare("SELECT tipo_baja_id FROM rel_motivo_baja WHERE id_motivo_baja = :motivobajaid");
//            $consulta->bindParam(':motivobajaid', $motivobajaid, PDO::PARAM_STR);
//            $consulta->execute();
//            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
//            return $resultados[0]['tipo_baja_id'];
//        }
//        function get_nombre_tipo_baja($DBSIGIE,$bajaid){
//            $consulta = $DBSIGIE->prepare("SELECT nombre FROM cat_tipo_bajas WHERE id_tipo_baja = :bajaid");
//            $consulta->bindParam(':bajaid', $bajaid, PDO::PARAM_STR);
//            $consulta->execute();
//            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
//            return $resultados[0]['nombre'];
//        }
//        //inicia conexión con SIGIE
//        $conexiondb = conection_db();
//        //consulta bajas activas por medio del userid Moodle
//        //obtener idpersona por medio del idMoodle
//        $idpersona = rel_personas_plataformas_moodle($conexiondb,$userid);
//
//        if($idpersona){
//            //obtener idevento por medio del id de cruso de moodle
//            $idevento = get_id_evento($conexiondb,$courseid);
//            if($idevento) {
//
//                //obtener bajas activas del usu
//                $motivobajaid = get_motivo_baja_id($conexiondb, $idpersona, $idevento);
//                if($motivobajaid) {
//
//                    //Motivo baja id
//                    $bajaid = get_tipo_baja($conexiondb, $motivobajaid);
//                    if($bajaid) {
//                        //obtener el nombre de el tipo de baja
//                        $porcentaje = get_nombre_tipo_baja($conexiondb, $bajaid);
//                        $porcentaje = 1;
//                    }
//                }
//            }
//        }
        return $porcentaje;

	}
	
	
	public static function courses_avance_oas_returns() {
		return new external_value(PARAM_INT, 'El avance de las medallas (El valor de la barra de las medallas)');
	}
	
	
		
	
	
}
