<?php
require_once("export.php");
require_once("generate_graph.php");
function generateReports($data,$data2=null,$nameReport='',$download=0,$hayGrafica=0){
    global $DB;
    $url = 'index.php';
    $nameImage = 'reporte_'.date('d_m_Y').'.png';
//        echo $data;
        if ($data == "all_user_plataform") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT IF (uid.data = "M","Mujer","Hombre") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
        FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 JOIN mdl_user AS u ON u.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id WHERE asg.roleid = 5 AND u.deleted = 0;
        ';
        }
        if ($data == "all_partipants_whithout_access") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT IF (uid.data = "M","Mujer","Hombre") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
        FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 JOIN mdl_user AS u ON u.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id WHERE asg.roleid = 5 AND u.deleted = 0 AND u.firstaccess = 0;
        ';
        }
        if ($data == "all_partipants_whith_one_access") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT IF (uid.data = "M","Mujer","Hombre") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
        FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 JOIN mdl_user AS u ON u.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id WHERE asg.roleid = 5 AND u.deleted = 0 AND u.firstaccess > 0;
        ';
        }
        if ($data == "all_partipants_in_n_courses_whithout_access") {
            //Querysql
            if($data2==null){
                $destination = "index.php";
                $message = "Debes agregar un id o id´s para realizar esté reporte";
                redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
            }
           $data2 = ".$data2.";
            $consulta = 'SELECT user2.id as ID, ul.timeaccess, user2.firstname AS "Nombre(s)", user2.lastname AS Apellidos, user2.email AS Correo,
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = user2.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Estado de residencia",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Sexo",
            c.fullname as "Nombre del curso",
            IF (user2.lastaccess = 0,"nunca accedio al curso", CONCAT("Accedio al sitio ",DATE_FORMAT(FROM_UNIXTIME(user2.lastaccess),"%Y-%m-%d"))) AS "Acceso al curso"
            ,(SELECT DATE_FORMAT(FROM_UNIXTIME(timeaccess),"%Y-%m-%d") FROM mdl_user_lastaccess WHERE userid=user2.id and courseid=c.id) as ultimo_acceso
            ,(SELECT r.name FROM mdl_user_enrolments AS uenrol JOIN mdl_enrol AS e ON e.id = uenrol.enrolid JOIN mdl_role AS r ON e.id = r.id WHERE uenrol.userid=user2.id and e.courseid = c.id) AS Nombre_del_rol
            FROM mdl_user_enrolments as ue JOIN mdl_enrol as e on e.id = ue.enrolid JOIN mdl_course as c ON c.id = e.courseid JOIN mdl_user as user2 ON user2 .id = ue.userid LEFT JOIN mdl_user_lastaccess as ul on ul.userid = user2.id
             WHERE c.id IN (2) AND ul.timeaccess IS NULL ';
        }
        if ($data == "all_partipants_active_one_course_n_days") {
            //Querysql
            if($data2==null){
                $destination = "index.php";
                $message = "Debes agregar el o los días e id del curso realizar esté reporte";
                redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
            }else{
                if(strstr($data2,",")){
                    $dataArray = explode(",",$data2);
                }
                if(sizeof($dataArray)==2){
                    $days = $dataArray[0];
                    $idcurso = $dataArray[1];
                }else{
                    $destination = "index.php";
                    $message = "Debes agregar el o los días e id del curso con el formato correcto";
                    redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
                }

            }
            $consulta = 'SELECT user2.id as ID, user2.firstname AS "Nombre(s)", user2.lastname AS Apellidos, user2.email AS Correo,
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = user2.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Estado de residencia",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Sexo",
            c.fullname as "Nombre del curso",
            IF (user2.lastaccess = 0,"nunca accedio al curso", CONCAT("Accedio al sitio ",DATE_FORMAT(FROM_UNIXTIME(user2.lastaccess),"%Y-%m-%d"))) AS "Acceso al curso"
            ,(SELECT DATE_FORMAT(FROM_UNIXTIME(timeaccess),"%Y-%m-%d")
              FROM mdl_user_lastaccess WHERE userid=user2.id and courseid=c.id) as ultimoacceso
            FROM mdl_user_enrolments as ue JOIN mdl_enrol as e on e.id = ue.enrolid JOIN mdl_course as c ON c.id = e.courseid JOIN mdl_user as user2 ON user2 .id = ue.userid LEFT JOIN mdl_user_lastaccess as ul on ul.userid = user2.id
             WHERE c.id = '.$idcurso.' AND FROM_UNIXTIME(ul.timeaccess, "%Y-%m-%d") >= DATE_SUB(NOW(), INTERVAL '.$days.' DAY)';
        }
        if ($data == "all_users_active_n_days") {
        //Querysql
        if($data2==null){
            $destination = "index.php";
            $message = "Debes agregar un id o id´s para realizar esté reporte";
            redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
        }
        $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos",FROM_UNIXTIME(`lastlogin`) as "fecha de acceso"
        FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 JOIN mdl_user AS u ON u.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id WHERE asg.roleid = 5 AND u.deleted = 0 AND DATEDIFF( NOW(),FROM_UNIXTIME(`lastlogin`) ) < '.$data2.' ;
        ';
    }
        if ($data == "all_participants_in_actual_category_and_old") {
        //Querysql
        if($data2==null){
            $destination = "index.php";
            $message = "Debes agregar el o los días e id del curso realizar esté reporte";
            redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
        }else{
            if(strstr($data2,",") AND strstr($data2,"|")){
                $dataArray = explode("|",$data2);
            }
            if(sizeof($dataArray)==3){
                $oldcategory = $dataArray[0];
                $actualcategory = $dataArray[1];
                $limiteids = $dataArray[2];
                $dataArray2 = explode(",",$limiteids);
                $idinit = $dataArray2[0];
                $idfinal = $dataArray2[1];
            }else{
                $destination = "index.php";
                $message = "Debes agregar el o los días e id del curso con el formato correcto";
                redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
            }

        }
        $consulta = 'SELECT us.firstname as "Nombre(s)" , us.lastname as "Apellidos", 
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = us.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = us.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = us.id and uid.fieldid = uif.id ) as "Sexo",
        IF (co.id BETWEEN '.$idinit.' AND '.$idfinal.',"Trimestre Anterior","Trimestre Actual")as "Trimestre", co.id as "Id del curos", co.fullname as "Nombre del curso"
        FROM mdl_course AS co 
        JOIN mdl_context AS ctx ON co.id = ctx.instanceid 
        JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id 
        JOIN mdl_user AS us ON us.id = ra.userid 
        WHERE co.category IN ('.$oldcategory.','.$actualcategory.')
        ORDER BY us.id ';
    }
        if ($data == "count_participants_per_groups") {
        //Querysql
        $consulta = 'SELECT  groups.name,course.fullname as "Nombre del curso",COUNT(u.id) as "No. de participantes"
        FROM mdl_role_assignments AS asg
        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50
        JOIN mdl_user AS u ON u.id = asg.userid
        JOIN mdl_course AS course ON context.instanceid = course.id
        JOIN mdl_course_categories as category ON category.id = course.category
        JOIN mdl_groups as groups ON groups.courseid = course.id
        JOIN mdl_groups_members as groups_m On groups_m.groupid = groups.id AND groups_m.userid=u.id
        WHERE asg.roleid = 5 AND u.deleted = 0 AND category.id = '.$data2.'
        group by groups.id';
        }
        if ($data == "count_participants_per_groups_final") {
            if(strstr($data2,",")){
                $dataArray = explode(",",$data2);
            }
            if(sizeof($dataArray)==2){
                $categoryid = $dataArray[0];
                $namegroup = $dataArray[1];
            }
        //Querysql
        $consulta = 'SELECT course.fullname as "Nombre del curso",COUNT(u.id) as "No. de participantes"
        FROM mdl_role_assignments AS asg
        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50
        JOIN mdl_user AS u ON u.id = asg.userid
        JOIN mdl_course AS course ON context.instanceid = course.id
        JOIN mdl_course_categories as category ON category.id = course.category
        JOIN mdl_groups as groups ON groups.courseid = course.id
        JOIN mdl_groups_members as groups_m On groups_m.groupid = groups.id AND groups_m.userid=u.id
        WHERE asg.roleid = 5 AND u.deleted = 0 AND category.id = '.$categoryid.' AND RIGHT(groups.name, 3) = "'.$namegroup.'"
        group by groups.id';
        }
        if ($data == "report_three_in_one") {
        //Querysql
        $consulta = 'SELECT  groups.name,course.fullname as "Nombre del curso",COUNT(u.id) as "No. de participantes"
        FROM mdl_role_assignments AS asg
        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50
        JOIN mdl_user AS u ON u.id = asg.userid
        JOIN mdl_course AS course ON context.instanceid = course.id
        JOIN mdl_course_categories as category ON category.id = course.category
        JOIN mdl_groups as groups ON groups.courseid = course.id
        JOIN mdl_groups_members as groups_m On groups_m.groupid = groups.id AND groups_m.userid=u.id
        WHERE asg.roleid = 5 AND u.deleted = 0 AND category.id = '.$data2.'
        group by groups.id';
    }
        if ($data == "custom_sql") {
        //Querysql

        $consulta = ''.$data2.'';
    }

        if($consulta != '') {
            //Se ejecuta la consulta
            $query = $DB->get_records_sql($consulta);
            //Validación para pintar lña gráfica
            if ($hayGrafica == 1) {
                createGraph($query, $nameReport, $download);
            }
            //Crea el archivo excel
            createExport($query, $nameReport, $download, $nameImage, $hayGrafica);
        }
        else{
            $destination = "index.php";
            $message = "No existe una consulta asociada a el item seleccionado, revistar el archivo datos.php";
            redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
        }



}