<?php

defined ( 'MOODLE_INTERNAL' ) || die ();

require_once($CFG->libdir.'/externallib.php');

class local_actions extends external_api {

    public static function restore_course_parameters() {
        return new external_function_parameters(
            array(
                'categoryid' => new external_value(PARAM_INT, 'Id de categoría donde se desea restaurar el curso'),
                'newcourse' => new external_value(PARAM_INT, 'Para indicar que se desea crear un curso nuevo 1 => si 0 => no'),
                'courseid' => new external_value(PARAM_INT, 'Id de curso donde se desea restaurar la copia', VALUE_OPTIONAL),
                'filepath' => new external_value(PARAM_RAW, 'Path donde se encuentra el respaldo que se desea restaurar'),
                'userid' => new external_value(PARAM_INT, 'id del usuario que esta realizando la restauración'),
                'data' => new  external_single_structure(
                    array(
                        'users' => new external_value(PARAM_INT, 'Indica si se realiza el restauración con usuarios (SI = 1) o sin datos (NO = 0)', VALUE_OPTIONAL),
                        'enrolments' => new external_value(PARAM_INT, 'Incluir el restauración con metodos de incripción (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'role_assignments' => new external_value(PARAM_INT, 'Incluir restauración con roles (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'permissions' => new external_value(PARAM_INT, 'Incluir restauración con permisos (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'activities' => new external_value(PARAM_INT, 'Incluir restauración con actividades (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'blocks' => new external_value(PARAM_INT, 'Incluir restauración con bloques (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'filters' => new external_value(PARAM_INT, 'Incluir restauración con filtros (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'comments' => new external_value(PARAM_INT, 'Incluir restauración con comentarios (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'badges' => new external_value(PARAM_INT, 'Incluir restauración con logros (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'calendarevents' => new external_value(PARAM_INT, 'Incluir restauración calendario de eventos (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'userscompletion' => new external_value(PARAM_INT, 'Incluir restauración con información de usuario (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'logs' => new external_value(PARAM_INT, 'Incluir restauración con logs de curso (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'grade_histories' => new external_value(PARAM_INT, 'Incluir restauración con historial de califciaciones (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'groups' => new external_value(PARAM_INT, 'Incluir restauración con grupos (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'competencies' => new external_value(PARAM_INT, 'Incluir restauración con competencias (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'customfields' => new external_value(PARAM_INT, 'Incluir restauración con campos perzonalidos (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'legacyfiles' => new external_value(PARAM_INT, 'Incluir restauración con archivos (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'overwrite_conf' => new external_value(PARAM_INT, 'Incluir restauración con configruacion (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'course_fullname' => new external_value(PARAM_RAW, 'Incluir restauración con nombre completo de curso (indicar el nombre complaeto de curso)', VALUE_OPTIONAL),
                        'course_shortname' => new external_value(PARAM_RAW, 'Incluir restauración con nombre corto de curso (indicar el nombre corto de curso)', VALUE_OPTIONAL),
                        'course_startdate' => new external_value(PARAM_RAW, 'Incluir restauración con fecha de inicio de curso (fecha en timestap)', VALUE_OPTIONAL),
                        'keep_roles_and_enrolments' => new external_value(PARAM_INT, 'Incluir restauración omitiendo los roles y matriculaciones (SI = 1, NO = 0)', VALUE_OPTIONAL),
                        'keep_groups_and_groupings' => new external_value(PARAM_INT, 'Incluir restauraciónomitiendo los grupos y agrupamientos (SI = 1, NO = 0)', VALUE_OPTIONAL),
                    )
                ,'Data para modificar el respaldo',VALUE_OPTIONAL)
            )
        );
    }

    public static function restore_course($categoryid = 1,$newCourse = 0,$courseid = 0, $filepath, $userid, $data = array() )
    {
        global $CFG;
        require_once($CFG->dirroot . "/backup/util/includes/restore_includes.php");
        // Validate the parameters
        $params = self::validate_parameters(self::restore_course_parameters(), array(
        'categoryid'=>$categoryid,
        'newcourse'=>$newCourse,
        'courseid' => $courseid,
        'filepath' => $filepath,
        'userid' => $userid,
        'data' => $data
        ));

        $context = context_course::instance($params['courseid']);
        self::validate_context($context);

        global $DB,$CFG;
        if (!$admin = get_admin()) {
            return 'error_'.get_string('noadmins','error');
        }

        if (!$category = $DB->get_record('course_categories', ['id' => $categoryid], 'id')) {
            return 'error_' .get_string('invalidcategoryid','error');
        }

        //Valida el nombre de curso para evitra duplicados
        if($courseid != 0 AND $newCourse == 0){
            if (!$shortnamefind = $DB->get_record('course', ['id' => $courseid], 'shortname')) {
                return 'error_'.get_string('invalidcourse','error');
            }
            if (!$validateCourseandCategoryid = $DB->get_record('course', ['id' => $courseid, 'category'=>$categoryid], '*')) {
                return 'error_invalid_relation_courseid_and_categoryid';
            }
        }else if($newCourse == 1) {
            if (isset($data['course_shortname'])) {
                $shortnamefind = $DB->get_record('course', ['shortname' => $data['course_shortname']], 'shortname');
                if ($shortnamefind) {
                    if ($shortnamefind->shortname == $data['course_shortname']) {
                        return 'course_shortname:' . $data['course_shortname'] . '_is_duplicate';
                    }
                }
            }
            if (isset($data['course_fullname'])) {
                $fullnamecourse = $DB->get_record('course', ['fullname' => $data['course_fullname']], 'fullname');
                if ($fullnamecourse) {
                    if ($fullnamecourse->fullname == $data['course_fullname']) {
                        return 'course_fullname:' . $data['course_fullname'] . '_is_duplicate';
                    }
                }
            }
        }

        /*if (!file_exists($filepath)) {
           return 'error_file_not_found';
        }*/

        $backupdir = restore_controller::get_tempdir_name(SITEID, $userid);
        $path = make_backup_temp_directory($backupdir);

        $fp = get_file_packer('application/vnd.moodle.backup');
        $fp->extract_to_pathname($filepath, $path);
        try {

            list($fullname, $shortname) = restore_dbops::calculate_course_names(0, get_string('restoringcourse', 'backup'),
                get_string('restoringcourseshortname', 'backup'));
            if($newCourse == 1) {
                $courseidnew = restore_dbops::create_new_course($fullname, $shortname, $category->id);
                $target = backup::TARGET_NEW_COURSE;
            }else{
                if($courseid == 0){
                    return 'error_'.get_string('invalidcourse','error');
                }
                if (!$coursevalidate = $DB->get_record('course', array('id' => $courseid))) {
                    return 'error_'.get_string('invalidcourse','error');
                }
                $courseidnew = $coursevalidate->id;
                $target = backup::TARGET_CURRENT_ADDING;
            }
            $rc = new restore_controller($backupdir, $courseidnew, backup::INTERACTIVE_NO,
                backup::MODE_GENERAL, $admin->id,$target );

            $rc->execute_precheck();
            //Para la opciones de restauración
            $plan = $rc->get_plan();
            // Obtener la lista de pasos en el plan y modificar las opciones según sea necesario
            foreach ($plan->get_tasks() as $task) {
                $settings = $task->get_settings();
                foreach ($settings as $setting) {
                    // Modificar las opciones de restauración

                    if ($setting->get_name() == 'users') {
                        if(isset($data['users']) AND is_number($data['users'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['users']);
                        }
                    }
                    if ($setting->get_name() == 'enrolments') {
                        if(isset($data['enrolments']) AND is_number($data['enrolments'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['enrolments']);
                        }
                    }if ($setting->get_name() == 'role_assignments') {
                        if(isset($data['role_assignments']) AND is_number($data['role_assignments'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['role_assignments']);
                        }
                    }if ($setting->get_name() == 'permissions') {
                        if(isset($data['permissions']) AND is_number($data['permissions'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['permissions']);
                        }
                    }if ($setting->get_name() == 'activities') {
                        if(isset($data['activities']) AND is_number($data['activities'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['activities']);
                        }
                    }if ($setting->get_name() == 'blocks') {
                        if(isset($data['blocks']) AND is_number($data['blocks'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['blocks']);
                        }
                    }if ($setting->get_name() == 'filters') {
                        if(isset($data['filters']) AND is_number($data['filters'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['filters']);
                        }
                    }if ($setting->get_name() == 'comments') {
                        if(isset($data['comments']) AND is_number($data['comments'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['comments']);
                        }
                    }if ($setting->get_name() == 'badges') {
                        if(isset($data['badges']) AND is_number($data['badges'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['badges']);
                        }
                    }if ($setting->get_name() == 'calendarevents') {
                        if(isset($data['calendarevents']) AND is_number($data['calendarevents'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['calendarevents']);
                        }
                    }if ($setting->get_name() == 'userscompletion') {
                        if(isset($data['userscompletion']) AND is_number($data['userscompletion'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['userscompletion']);
                        }
                    }if ($setting->get_name() == 'logs') {
                        if(isset($data['logs']) AND is_number($data['logs'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['logs']);
                        }
                    }if ($setting->get_name() == 'grade_histories') {
                        if(isset($data['grade_histories']) AND is_number($data['grade_histories'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['grade_histories']);
                        }
                    }if ($setting->get_name() == 'groups') {
                        if(isset($data['groups']) AND is_number($data['groups'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['groups']);
                        }
                    }if ($setting->get_name() == 'competencies') {
                        if(isset($data['competencies']) AND is_number($data['competencies'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['competencies']);
                        }
                    }if ($setting->get_name() == 'customfields') {
                        if(isset($data['customfields']) AND is_number($data['customfields'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['customfields']);
                        }
                    }if ($setting->get_name() == 'contentbankcontent') {
                        if(isset($data['contentbankcontent']) AND is_number($data['contentbankcontent'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['contentbankcontent']);
                        }
                    }if ($setting->get_name() == 'legacyfiles') {
                        if(isset($data['legacyfiles']) AND is_number($data['legacyfiles'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['legacyfiles']);
                        }
                    }if ($setting->get_name() == 'overwrite_conf') {
                        if(isset($data['overwrite_conf']) AND is_number($data['overwrite_conf'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['overwrite_conf']);
                        }
                    }if ($setting->get_name() == 'course_fullname') {
                        if(isset($data['course_fullname'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['course_fullname']);
                        }
                    }if ($setting->get_name() == 'course_shortname') {
                        if(isset($data['course_shortname'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['course_shortname']);
                        }
                    }if ($setting->get_name() == 'course_startdate') {
                        if(isset($data['course_startdate'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['course_startdate']);
                        }
                    }if ($setting->get_name() == 'keep_roles_and_enrolments') {
                        if(isset($data['keep_roles_and_enrolments']) AND is_number($data['keep_roles_and_enrolments'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['keep_roles_and_enrolments']);
                        }
                    }if ($setting->get_name() == 'keep_groups_and_groupings') {
                        if(isset($data['keep_groups_and_groupings']) AND is_number($data['keep_groups_and_groupings'])){
                            //1 => restaura 0 => no restaura
                            $setting->set_value($data['keep_groups_and_groupings']);
                        }
                    }
                }

            }
            $rc->execute_plan();
            $rc->destroy();

        } catch (Exception $e) {
            fulldelete($path);
            return get_string('generalexceptionmessage', 'error').$e->getMessage();
        }
        return  'success_idcourse_'.$courseidnew;
    }

    public static function restore_course_returns() {
        return  new external_value(PARAM_TEXT, 'Status of the restore operation');
    }



    public static function mass_restore_courses_parameters() {
        return new external_function_parameters(
            array(
                'filepath' => new external_value(PARAM_RAW, 'Ruta absoluta del archivo MBZ en el servidor Moodle', VALUE_DEFAULT, ''),
                'mbzbase64' => new external_value(PARAM_RAW, 'Contenido del archivo MBZ codificado en base64', VALUE_DEFAULT, ''),
                'mbzfilename' => new external_value(PARAM_FILE, 'Nombre de archivo MBZ cuando se usa mbzbase64', VALUE_DEFAULT, 'restore.mbz'),
                'mbzurl' => new external_value(PARAM_URL, 'URL HTTPS del MBZ para descarga en servidor Moodle', VALUE_DEFAULT, ''),
                'userid' => new external_value(PARAM_INT, 'Usuario que ejecuta la restauración'),
                'mode' => new external_value(PARAM_ALPHA, 'Modo de restauración: merge o replace', VALUE_DEFAULT, 'merge'),
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'ID del curso destino')
                ),
                'data' => new external_single_structure(
                    array(
                        'users' => new external_value(PARAM_INT, 'Incluir usuarios', VALUE_OPTIONAL),
                        'enrolments' => new external_value(PARAM_INT, 'Incluir matriculaciones', VALUE_OPTIONAL),
                        'role_assignments' => new external_value(PARAM_INT, 'Incluir roles', VALUE_OPTIONAL),
                        'activities' => new external_value(PARAM_INT, 'Incluir actividades', VALUE_OPTIONAL),
                        'blocks' => new external_value(PARAM_INT, 'Incluir bloques', VALUE_OPTIONAL),
                        'filters' => new external_value(PARAM_INT, 'Incluir filtros', VALUE_OPTIONAL),
                        'comments' => new external_value(PARAM_INT, 'Incluir comentarios', VALUE_OPTIONAL),
                        'groups' => new external_value(PARAM_INT, 'Incluir grupos', VALUE_OPTIONAL),
                        'keep_roles_and_enrolments' => new external_value(PARAM_INT, 'Conservar roles/matriculaciones', VALUE_OPTIONAL),
                        'keep_groups_and_groupings' => new external_value(PARAM_INT, 'Conservar grupos/agrupamientos', VALUE_OPTIONAL),
                    ),
                    'Opciones del plan de restauración',
                    VALUE_DEFAULT,
                    array()
                )
            )
        );
    }

    public static function mass_restore_courses($filepath = '', $mbzbase64 = '', $mbzfilename = 'restore.mbz', $mbzurl = '', $userid = 0, $mode = 'merge', $courseids = array(), $data = array()) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/backup/util/includes/restore_includes.php");

        $params = self::validate_parameters(self::mass_restore_courses_parameters(), array(
            'filepath' => $filepath,
            'mbzbase64' => $mbzbase64,
            'mbzfilename' => $mbzfilename,
            'mbzurl' => $mbzurl,
            'userid' => $userid,
            'mode' => $mode,
            'courseids' => $courseids,
            'data' => $data,
        ));

        if (!in_array($params['mode'], array('merge', 'replace'))) {
            throw new invalid_parameter_exception('Invalid mode, use merge or replace');
        }

        $filepathlocal = $params['filepath'];
        if ($filepathlocal === '' && $params['mbzbase64'] !== '') {
            $tmpdir = make_request_directory();
            $filepathlocal = $tmpdir . '/' . clean_param($params['mbzfilename'], PARAM_FILE);
            $rawfile = base64_decode($params['mbzbase64'], true);
            if ($rawfile === false) {
                throw new invalid_parameter_exception('mbzbase64 is not valid base64');
            }
            file_put_contents($filepathlocal, $rawfile);
        } else if ($filepathlocal === '' && $params['mbzurl'] !== '') {
            $tmpdir = make_request_directory();
            $filepathlocal = $tmpdir . '/' . clean_param($params['mbzfilename'], PARAM_FILE);
            $downloaded = download_file_content($params['mbzurl']);

            // Fallback for local URLs when web server/loopback routing blocks curl download.
            if ($downloaded === false || $downloaded === '') {
                $urlparts = parse_url($params['mbzurl']);
                if (!empty($urlparts['host']) && in_array($urlparts['host'], array('localhost', '127.0.0.1')) && !empty($urlparts['path'])) {
                    $localpath = rtrim($CFG->dirroot, '/') . $urlparts['path'];
                    if (file_exists($localpath) && is_readable($localpath)) {
                        $downloaded = file_get_contents($localpath);
                    }
                }
            }

            if ($downloaded === false || $downloaded === '') {
                throw new moodle_exception('filenotfound');
            }

            file_put_contents($filepathlocal, $downloaded);
        }

        if ($filepathlocal === '' || !file_exists($filepathlocal) || !is_readable($filepathlocal)) {
            throw new moodle_exception('filenotfound');
        }

        if (!$admin = get_admin()) {
            throw new moodle_exception('noadmins', 'error');
        }

        $results = array();
        foreach ($params['courseids'] as $courseid) {
            $context = context_course::instance($courseid);
            self::validate_context($context);
            require_capability('moodle/restore:restorecourse', $context);

            $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

            $backupdir = restore_controller::get_tempdir_name(SITEID, $params['userid']) . '_' . $courseid . '_' . time();
            $path = make_backup_temp_directory($backupdir);
            $fp = get_file_packer('application/vnd.moodle.backup');
            $fp->extract_to_pathname($filepathlocal, $path);

            try {
                $target = $params['mode'] === 'replace' ? backup::TARGET_EXISTING_DELETING : backup::TARGET_CURRENT_ADDING;
                $rc = new restore_controller($backupdir, $course->id, backup::INTERACTIVE_NO, backup::MODE_GENERAL, $admin->id, $target);
                $rc->execute_precheck();

                $plan = $rc->get_plan();
                foreach ($plan->get_tasks() as $task) {
                    foreach ($task->get_settings() as $setting) {
                        $name = $setting->get_name();
                        if (array_key_exists($name, $params['data']) && is_number($params['data'][$name])) {
                            $setting->set_value($params['data'][$name]);
                        }
                    }
                }

                $rc->execute_plan();
                $rc->destroy();
                fulldelete($path);

                $results[] = array('courseid' => $courseid, 'status' => 'success', 'message' => 'Restauración completada');
            } catch (Exception $e) {
                fulldelete($path);
                $results[] = array('courseid' => $courseid, 'status' => 'error', 'message' => $e->getMessage());
            }
        }

        return $results;
    }

    public static function mass_restore_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'courseid' => new external_value(PARAM_INT, 'Curso destino'),
                'status' => new external_value(PARAM_ALPHA, 'success o error'),
                'message' => new external_value(PARAM_TEXT, 'Mensaje de resultado')
            ))
        );
    }

    public static function backup_course_parameters() {
        return new external_function_parameters(
            array(
                'courseid' => new external_value(PARAM_INT, 'Course ID to backup')
            )
        );
    }

    public static function backup_course($courseid) {
        global $DB, $USER, $CFG;

        // Incluir las librerías necesarias para el backup
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->libdir . '/filestorage/file_storage.php');

        // Validar los parámetros
        $params = self::validate_parameters(self::backup_course_parameters(), array('courseid' => $courseid));

        // Obtener el contexto del curso
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);

        // Verificar los permisos del usuario (si es necesario)
        // require_capability('moodle/course:backup', $context);

        // Cargar el curso
        $course = $DB->get_record('course', array('id' => $params['courseid']), '*', MUST_EXIST);

        // Crear el controlador de backup
        $bc = new backup_controller(backup::TYPE_1COURSE, $course->id, backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO, backup::MODE_GENERAL, $USER->id);

        // Configurar opciones de respaldo
        $bc->get_plan()->get_setting('users')->set_value(0); // No incluir usuarios
        $bc->get_plan()->get_setting('activities')->set_value(1); // Incluir actividades
        $bc->get_plan()->get_setting('blocks')->set_value(1); // Incluir bloques
        $bc->get_plan()->get_setting('filters')->set_value(1); // Incluir filtros

        // Ejecutar el plan de respaldo
        $bc->execute_plan();

        // Obtener los resultados del backup, incluyendo el archivo .mbz
        $results = $bc->get_results();
        $backup_file = $results['backup_destination']; // Objeto de tipo 'stored_file'

        // Obtener el contenido del archivo .mbz y guardarlo en una ruta personalizada
        if ($backup_file) {
            // Definir la ruta de destino donde se debe guardar el archivo
            $dest_folder = $CFG->dataroot . '/backups/';
            $dest_file = $dest_folder . 'backup_' . $course->id . '.mbz';

            // Si la carpeta de destino no existe, crearla
            if (!file_exists($dest_folder)) {
                mkdir($dest_folder, 0777, true);
            }

            // Obtener el contenido del archivo y guardarlo en la carpeta de destino
            $file_content = $backup_file->get_content(); // Obtener el contenido binario del archivo
            file_put_contents($dest_file, $file_content); // Guardar el contenido en el archivo de destino

            // Retornar la ubicación del archivo guardado
            return $dest_file;
        } else {
            return 'error_no_found_backup_file';
        }

        // Finalizar el controlador
        $bc->destroy();
    }

    public static function backup_course_returns() {
        return  new external_value(PARAM_TEXT, 'Status of the backup operation');
    }
}
