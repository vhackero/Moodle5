<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function generateReports($data,$data2=null,$rolnumber=5){
    global $DB;
    $url = 'index.php';
    $nameImage = 'reporte_'.date('d_m_Y').'.png';
//        echo $data;
        if ($data == "all_user_plataform") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT IF (uid.data = "M","Mujer",IF (uid.data = "H","Hombre","")) FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
        FROM mdl_user as u WHERE u.deleted = 0
        ORDER BY u.id;
        ';
        }
        if ($data == "all_user_plataform_rol") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT IF (uid.data = "M","Mujer",IF (uid.data = "H","Hombre","")) FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
        FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 JOIN mdl_user AS u ON u.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0
        ORDER BY u.id;
        ';
        }
        if ($data == "all_users_whithout_access") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT IF (uid.data = "M","Mujer",IF (uid.data = "H","Hombre","")) FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
        FROM mdl_user as u WHERE u.deleted = 0 AND u.firstaccess = 0
        ORDER BY u.id;
        ';
        }
        if ($data == "all_partipants_whithout_access") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
            ( SELECT IF (uid.data = "M","Mujer","Hombre") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
            FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 JOIN mdl_user AS u ON u.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0 AND u.firstaccess = 0;
            ';
        }
        if ($data == "all_users_whith_one_access") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT IF (uid.data = "M","Mujer",IF (uid.data = "H","Hombre","")) FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
        FROM mdl_user as u WHERE u.deleted = 0 AND u.firstaccess > 0
        ORDER BY u.id;';
        }
        if ($data == "all_partipants_whith_one_access") {
            //Querysql
            $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos", u.email as "Correo",
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
            ( SELECT IF (uid.data = "M","Mujer","Hombre") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo"
            FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 JOIN mdl_user AS u ON u.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0 AND u.firstaccess > 0;
            ';
        }
        if ($data == "all_partipants_in_n_courses_whithout_access") {
            //Querysql
            if($data2==null){
                $destination = "../index.php";
                $message = "Debes agregar un id o id´s para realizar esté reporte";
                redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
            }
            $datos = explode(",",$data2);
            if(sizeof($datos) == 1){
                $data2 = (int)$data2;
            }
            $consulta = 'SELECT user2.id as ID, user2.firstname AS "Nombre(s)", user2.lastname AS Apellidos, user2.email AS Correo,
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = user2.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Estado de residencia",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Sexo",
            c.fullname as "Nombre del curso"
            FROM mdl_user_enrolments as ue 
            JOIN mdl_enrol as e on e.id = ue.enrolid 
            JOIN mdl_course as c ON c.id = e.courseid 
            JOIN mdl_user as user2 ON user2 .id = ue.userid 
            LEFT JOIN mdl_user_lastaccess as ul on ul.userid = user2.id AND ul.courseid = c.id
             WHERE  c.id IN ('.$data2.') AND ul.timeaccess IS NULL
             ORDER BY ul.timeaccess';
        }
        if ($data == "all_partipants_in_n_courses_whithout_access_not_lista_espera") {
            //Querysql
            if($data2==null){
                $destination = "../index.php";
                $message = "Debes agregar un id o id´s para realizar esté reporte";
                redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
            }
            $datos = explode(",",$data2);
            if(sizeof($datos) == 1){
                $data2 = (int)$data2;
            }
            $consulta = 'SELECT user2.id as ID, user2.firstname AS "Nombre(s)", user2.lastname AS Apellidos, user2.email AS Correo,
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = user2.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Estado de residencia",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Sexo",
            c.fullname as "Nombre del curso"
            FROM mdl_user_enrolments as ue 
            JOIN mdl_enrol as e on e.id = ue.enrolid 
            JOIN mdl_course as c ON c.id = e.courseid 
            JOIN mdl_groups as gr on gr.courseid = c.id
            JOIN mdl_groups_members as gm on gm.groupid = gr.id AND gm.userid = ue.userid
            JOIN mdl_user as user2 ON user2 .id = ue.userid 
            LEFT JOIN mdl_user_lastaccess as ul on ul.userid = user2.id AND ul.courseid = c.id
             WHERE  c.id IN ('.$data2.') AND ul.timeaccess IS NULL AND gr.name NOT LIKE "%Lista%"
             ORDER BY ul.timeaccess';
        }
        if ($data == "all_partipants_active_one_course_n_days") {
            //Querysql
            if($data2==null){
                $destination = "../index.php";
                $message = "Debes agregar el o los días e id del curso realizar esté reporte";
                redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
            }else{
                if(strstr($data2,"|")){
                    $dataArray = explode("|",$data2);

                }
                if(sizeof($dataArray)==2){
                    $days = $dataArray[0];
                    $idcurso = $dataArray[1];
                }else{
                    $destination = "../index.php";
                    $message = "Debes agregar el o los días e id del curso con el formato correcto";
                    redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
                }

            }
            $consulta = 'SELECT user2.id as ID, user2.firstname AS "Nombre(s)", user2.lastname AS Apellidos, user2.email AS Correo,
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = user2.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Estado de residencia",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Sexo",
            c.fullname as "Nombre del curso",
            IF (user2.lastaccess = 0,"nunca accedio al curso", CONCAT("Accedio al curso ",DATE_FORMAT(FROM_UNIXTIME(user2.lastaccess),"%d-%m-%Y"))) AS "Acceso al curso"
            ,(SELECT DATE_FORMAT(FROM_UNIXTIME(timeaccess),"%d-%m-%Y")
              FROM mdl_user_lastaccess WHERE userid=user2.id and courseid=c.id) as ultimoacceso
            FROM mdl_user_enrolments as ue JOIN mdl_enrol as e on e.id = ue.enrolid JOIN mdl_course as c ON c.id = e.courseid JOIN mdl_user as user2 ON user2 .id = ue.userid LEFT JOIN mdl_user_lastaccess as ul on ul.userid = user2.id and ul.courseid = c.id
            JOIN mdl_role_assignments AS asg ON asg.userid = user2.id
        	JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 AND context.instanceid = c.id
             WHERE c.id IN '.$idcurso.' AND FROM_UNIXTIME(ul.timeaccess, "%Y-%m-%d") >= DATE_SUB(NOW(), INTERVAL '.$days.' DAY) AND asg.roleid = '.$rolnumber.'
      ORDER BY c.id';
        }
        if ($data == "all_users_active_n_days") {
        //Querysql
        if($data2==null){
            $destination = "../index.php";
            $message = "Debes agregar un id o id´s para realizar esté reporte";
            redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
        }
        $consulta = 'SELECT DISTINCT(u.id), u.firstname as "Nombre", u.lastname as "Apellidos",u.email as "Correo",DATE_FORMAT(FROM_UNIXTIME(`lastlogin`),"%d-%m-%Y") as "fecha de acceso"
        FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 JOIN mdl_user AS u ON u.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0 AND DATEDIFF( NOW(),FROM_UNIXTIME(`lastlogin`) ) < '.$data2.' ;
        ';
    }
        if ($data == "all_participants_in_actual_category_and_old") {
        //Querysql
        if($data2==null){
            $destination = "../index.php";
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
                $destination = "../index.php";
                $message = "Debes agregar el o los días e id del curso con el formato correcto";
                redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
            }

        }
        $consulta = 'SELECT us.firstname as "Nombre(s)" , us.lastname as "Apellidos", us.email as "Correo",
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
        $consulta = 'SELECT  groups.name as "Nombre del grupo",course.fullname as "Nombre del curso",COUNT(u.id) as "No. de usuarios"
        FROM mdl_role_assignments AS asg
        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50
        JOIN mdl_user AS u ON u.id = asg.userid
        JOIN mdl_course AS course ON context.instanceid = course.id
        JOIN mdl_course_categories as category ON category.id = course.category
        JOIN mdl_groups as groups ON groups.courseid = course.id
        JOIN mdl_groups_members as groups_m On groups_m.groupid = groups.id AND groups_m.userid=u.id
        WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0 AND category.id = '.$data2.'
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
        $consulta = 'SELECT  groups.name,course.fullname as "Nombre del curso",COUNT(u.id) as `No. de participantes`
        FROM mdl_role_assignments AS asg
        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50
        JOIN mdl_user AS u ON u.id = asg.userid
        JOIN mdl_course AS course ON context.instanceid = course.id
        JOIN mdl_course_categories as category ON category.id = course.category
        JOIN mdl_groups as groups ON groups.courseid = course.id
        JOIN mdl_groups_members as groups_m On groups_m.groupid = groups.id AND groups_m.userid=u.id
        WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0 AND category.id = '.$categoryid.' AND RIGHT(groups.name, 3) = "'.$namegroup.'"
        group by course.id';
//        group by groups.id';
    }
        if ($data == "count_participants_per_groups_types") {
        //Querysql
        $consulta = 'SELECT  course.fullname as "Nombre del curso",CASE 
		WHEN groups.name LIKE "%lengua%" THEN "Comunidad de práctica de la lengua" 
        WHEN groups.name LIKE "%cultura%" THEN "Comunidad de práctica de la cultura"
        ELSE groups.name
        END
        as "Nombre del grupo",COUNT(u.id) as "No. de usuarios"
        FROM mdl_role_assignments AS asg
        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50
        JOIN mdl_user AS u ON u.id = asg.userid
        JOIN mdl_course AS course ON context.instanceid = course.id
        JOIN mdl_course_categories as category ON category.id = course.category
        JOIN mdl_groups as groups ON groups.courseid = course.id
        JOIN mdl_groups_members as groups_m On groups_m.groupid = groups.id AND groups_m.userid=u.id
        WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0 AND category.id = '.$data2.'
        group by CASE 
        WHEN groups.name LIKE "%Español%" THEN 1
        WHEN groups.name LIKE "%Inglés%" THEN 2
        WHEN groups.name LIKE "%Maya%" THEN 3
        WHEN groups.name LIKE "%Náhuatl%" THEN 4
        WHEN groups.name LIKE "%cultura%" AND course.fullname = "Maya" THEN 5
         WHEN groups.name LIKE "%cultura%" AND course.fullname = "Náhuatl" THEN 6
         WHEN groups.name LIKE "%lengua%" AND course.fullname = "Maya" THEN 7
         WHEN groups.name LIKE "%lengua%" AND course.fullname = "Náhuatl" THEN 8
          WHEN groups.name LIKE "%lengua%" AND course.fullname = "Español" THEN 9
           WHEN groups.name LIKE "%lengua%" AND course.fullname = "Inglés" THEN 10
        ELSE 11
        END';
    }
        if ($data == "report_all_data_with_course_and_group") {
        //Querysql
        $consulta = 'SELECT DISTINCT(u.id),u.username as "Nombre de Usuario", u.firstname as "Nombre", u.lastname as "Apellidos",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Curp" where uid.userid = u.id and uid.fieldid = uif.id ) as "Curp",
        u.email as "Correo",
        ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
        ( SELECT IF (uid.data = "M","Mujer","Hombre") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Sexo",
        u.city as "Ciudad",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de residencia" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de residencia",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de nacimiento" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de nacimiento",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "ocupacion" where uid.userid = u.id and uid.fieldid = uif.id ) as "Ocupacion",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "matricula" where uid.userid = u.id and uid.fieldid = uif.id ) as "Matricula",
        ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Rol" where uid.userid = u.id and uid.fieldid = uif.id ) as "Rol UnADM",
        course.fullname as "Curso",
        g.name as "Nombre del grupo"
        FROM mdl_role_assignments AS asg 
        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 
        JOIN mdl_user AS u ON u.id = asg.userid
        JOIN mdl_course AS course ON context.instanceid = course.id
        JOIN mdl_groups as g ON g.courseid=course.id
        JOIN mdl_groups_members gm On gm.groupid = g.id AND gm.userid = u.id
        WHERE asg.roleid ='.$rolnumber.' AND u.deleted = 0 AND course.category = '.$data2.'';
    }
        if($data == "report_week_access"){
            $consulta = "SELECT  groups.name as 'Nombre del grupo',course.fullname as 'Nombre del curso',COUNT(u.id) as 'No. de usuarios',sum(case when (ul.timeaccess > 0 AND FROM_UNIXTIME(ul.timeaccess, '%Y-%m-%d') >= DATE_SUB(NOW(), INTERVAL 10 DAY)) then 1 END) as 'Activos',sum(case when (ul.timeaccess IS NULL)  then 1 END) as 'Inactivos',sum(case when (ul.timeaccess > 0 AND FROM_UNIXTIME(ul.timeaccess, '%Y-%m-%d') <= DATE_SUB(NOW(), INTERVAL 11 DAY))  then 1 END) as 'Inactivos > 10 días'
        FROM mdl_role_assignments AS asg
        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50
        JOIN mdl_user AS u ON u.id = asg.userid
        JOIN mdl_course AS course ON context.instanceid = course.id
        JOIN mdl_course_categories as category ON category.id = course.category
        JOIN mdl_groups as groups ON groups.courseid = course.id
        JOIN mdl_groups_members as groups_m On groups_m.groupid = groups.id AND groups_m.userid=u.id
        LEFT JOIN mdl_user_lastaccess as ul on ul.userid = u.id AND ul.courseid = course.id
        WHERE asg.roleid = $rolnumber AND u.deleted = 0 AND course.id IN ($data2)
        GROUP BY groups.id
        ";
        }
        if($data == "report_status_acreditado"){
             //TODO revisar porque no se ejecuta la consulta
            $consulta = 'SELECT  u.firstname as "Nombre(s)", u.lastname as "Apellidos", 
            u.email as "Correo electrónico",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Curp" where uid.userid = u.id and uid.fieldid = uif.id ) as "CURP",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = u.id and uid.fieldid = uif.id ) as "Género",
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = u.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de nacimiento" where uid.userid = u.id and uid.fieldid = uif.id ) as "Estado de Residencia",
            ( SELECT IF(uid.data = "S", "Sí", "No") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Es hablante de alguna lengua índigena" where uid.userid = u.id and uid.fieldid = uif.id ) as "Es hablante de alguna lengua indígena",
            ( SELECT IF(uid.data = "S", "Sí", "No") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Tiene alguna discapacidad" where uid.userid = u.id and uid.fieldid = uif.id ) as "Tiene alguna discapacidad",
            ( SELECT IF(uid.data = "S", "Sí", "No") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "afiliada a un sindicato" where uid.userid = u.id and uid.fieldid = uif.id ) as "Es una persona trabajadora afiliada a un sindicato",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "nombre completo del sindicato" where uid.userid = u.id and uid.fieldid = uif.id ) as "Nombre completo del sindicato en el que se encuentra afiliado(a)",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "nombre de la empresa" where uid.userid = u.id and uid.fieldid = uif.id ) as "Centro laboral en el que trabaja",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "dispositivo sueles conectarte a internet" where uid.userid = u.id and uid.fieldid = uif.id ) as "Con qué dispositivo suele conectarte a internet ",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "nivel de dominio internet y computadora" where uid.userid = u.id and uid.fieldid = uif.id ) as "Nivel de dominio que considera tener en el uso de la computadora e internet:",
            ( SELECT IF(uid.data = "S", "Sí", "No") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "acceso a internet de manera cotidiana" where uid.userid = u.id and uid.fieldid = uif.id ) as "Cuenta con acceso a internet de manera cotidiana ",
            ( SELECT IF(uid.data = "S", "Sí", "No") FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "computadora en tu domicilio o en tu centro de trabajo" where uid.userid = u.id and uid.fieldid = uif.id ) as "Cuenta con computadora en su domicilio o en su centro de trabajo",
            ( SELECT IF(uid.data = "S", "Sí", "No")FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "participado en algún curso en plataforma virtual" where uid.userid = u.id and uid.fieldid = uif.id ) as "Ha participado en algún curso cuya forma de trabajo haya sido en plataforma virtual  ",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Nivel de estudios" where uid.userid = u.id and uid.fieldid = uif.id ) as "Nivel de estudios",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Forma parte de la UnADM" where uid.userid = u.id and uid.fieldid = uif.id ) as "Forma parte de la comunidad de la Universidad Abierta y a Distancia de México",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Por qué medio te enteraste de este curso" where uid.userid = u.id and uid.fieldid = uif.id ) as "Por qué medio te enteraste de este curso",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "el principal motivo por el que te inscribiste a este curso" where uid.userid = u.id and uid.fieldid = uif.id ) as "Cuál es el principal motivo por el que te inscribiste a este curso ",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Rol" where uid.userid = u.id and uid.fieldid = uif.id ) as "Rol",
            c.fullname as "Curso",
            IF((SELECT DISTINCT if(ras.id > 0, "Acreditado", "No_acreditado") 
            FROM  mdl_customcert as ccs  
            INNER JOIN mdl_customcert_issues as cis  ON ccs.id = cis.customcertid and ccs.course = c.id
            INNER JOIN mdl_course AS cs JOIN mdl_context AS ctxs ON cs.id = ctxs.instanceid
            INNER JOIN mdl_role_assignments AS ras ON ras.contextid = ctxs.id 
            INNER JOIN mdl_user as us on u.id = cis.userid AND us.id = ras.userid 
            WHERE ras.roleid = '.$rolnumber.' AND ctxs.instanceid = c.id AND us.id = u.id)
            IS NOT NULL, "Acreditado", "No_acreditado")as "Estado de acreditación del curso"
           
            FROM mdl_course AS c JOIN mdl_context AS ctx ON c.id = ctx.instanceid
             JOIN mdl_role_assignments AS ra ON ra.contextid = ctx.id
             JOIN mdl_role as r ON r.id =ra.roleid
             JOIN mdl_user AS u ON u.id = ra.userid 
            WHERE ra.roleid = '.$rolnumber.' AND ctx.instanceid = '.$data2.' and u.deleted = 0 AND u.address != ""';
        }
    if($data == "report_access_n_course_with_rol"){
        $consulta = 'SELECT DISTINCT user2.id as ID, user2.firstname AS "Nombre(s)", user2.lastname AS Apellidos, user2.email AS Correo,
            ( SELECT uid.data  FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Edad" where uid.userid = user2.id and uid.fieldid = uif.id )  as "Edad",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Estado de nacimiento" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Estado de residencia",
            ( SELECT uid.data FROM mdl_user_info_data as uid INNER JOIN mdl_user_info_field AS uif ON uif.name =  "Género" where uid.userid = user2.id and uid.fieldid = uif.id ) as "Sexo",
            c.fullname as "Nombre del curso",
            IF (ul.timeaccess > 0, CONCAT("Accedio al curso el ",DATE_FORMAT(FROM_UNIXTIME(ul.timeaccess),"%d-%m-%Y")),"Nunca accedio al curso") AS "Acceso al curso"
            FROM mdl_user_enrolments as ue 
            JOIN mdl_enrol as e on e.id = ue.enrolid 
            JOIN mdl_course as c ON c.id = e.courseid 
            JOIN mdl_user as user2 ON user2 .id = ue.userid
            LEFT JOIN mdl_user_lastaccess as ul on ul.userid = user2.id and ul.courseid = c.id
            JOIN mdl_role_assignments AS asg ON asg.userid = user2.id
        	JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 AND context.instanceid = c.id
             WHERE c.id IN ('.$data2.') AND ul.timeaccess > 0  AND asg.roleid = '.$rolnumber.'
             ORDER BY user2.firstname';
    }
    if ($data == "report_access_with_rol") {
        $datafechas = explode('|',$data2);
        $fechaincial = $datafechas[0];
        $fechaReordenada = DateTime::createFromFormat('d-m-Y', $fechaincial)->format('Y-m-d');
        $fechaincial = strtotime($fechaReordenada);
        $fechafinal = $datafechas[1].'23:59:59';
        $fechaReordenada = DateTime::createFromFormat('d-m-Y H:i:s', $fechafinal)->format('Y-m-d H:i:s');
        $fechafinal = strtotime($fechaReordenada);
        //Querysql
        $consulta = 'SELECT DISTINCT(u.id),course.fullname,  u.username as "Usuario", u.city as "Ciudad",IF(u.country = "MX","México",u.country)  as "País", DATE_FORMAT(FROM_UNIXTIME(u.firstaccess),"%d-%m-%Y") as "Primer fecha de Acceso"
        FROM mdl_role_assignments AS asg JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 
            JOIN mdl_user AS u ON u.id = asg.userid 
            JOIN mdl_course AS course ON context.instanceid = course.id 
        WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0 AND u.firstaccess > 0 AND (u.firstaccess >= '.$fechaincial.' AND u.firstaccess <= '.$fechafinal.')
        ORDER BY u.firstaccess
        ';
    }
    if ($data == "report_access_with_rol_in_course") {
        $datafechas = explode('|',$data2);
        $fechaincial = $datafechas[0];
        $fechaReordenada = DateTime::createFromFormat('d-m-Y', $fechaincial)->format('Y-m-d');
        $fechaincial = strtotime($fechaReordenada);
        $fechafinal = $datafechas[1].'23:59:59';
        $fechaReordenada = DateTime::createFromFormat('d-m-Y H:i:s', $fechafinal)->format('Y-m-d H:i:s');
        $fechafinal = strtotime($fechaReordenada);
        $courses =  $datafechas[2];
        //Querysql
        $consulta = 'SELECT DISTINCT(u.id), u.username as "Usuario", u.city as "Ciudad",IF(u.country = "MX","México",u.country)  as "País",course.id as "Id de curso", course.fullname as "Nombre del curso", DATE_FORMAT(FROM_UNIXTIME(ul.timeaccess),"%d-%m-%Y") as "Ultima actividad"
        FROM mdl_role_assignments AS asg 
            JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 
            JOIN mdl_user AS u ON u.id = asg.userid 
            JOIN mdl_course AS course ON context.instanceid = course.id 
            LEFT JOIN mdl_user_lastaccess as ul on ul.userid = u.id and ul.courseid = course.id
        WHERE asg.roleid = '.$rolnumber.' AND u.deleted = 0 AND ul.timeaccess > 0 AND course.id IN ('.$courses.')  AND (ul.timeaccess >= '.$fechaincial.' AND ul.timeaccess <= '.$fechafinal.')
        ORDER BY ul.timeaccess
        ';
    }
    if ($data == "report_paticipation_course") {
        $datafechas = explode('|',$data2);
        $fechaincial = $datafechas[0];
        $fechaReordenada = DateTime::createFromFormat('d-m-Y', $fechaincial)->format('Y-m-d');
        $fechaincial = strtotime($fechaReordenada);
        $fechafinal = $datafechas[1].'23:59:59';  // En caso de quere hacer el corte al final del día.
//        $fechafinal = $datafechas[1].'00:00:00'; En caso de quere hacer el corte al incio del día.
        $fechaReordenada = DateTime::createFromFormat('d-m-Y H:i:s', $fechafinal)->format('Y-m-d H:i:s');
        $fechafinal = strtotime($fechaReordenada);
        $courses =  $datafechas[2];
        //Querysql
        $consulta = 'SELECT
    courses.fullname,
    (SELECT COUNT(DISTINCT ue.userid) AS total_participants
     FROM mdl_user_enrolments ue
              JOIN mdl_enrol e ON ue.enrolid = e.id
              JOIN mdl_course c ON e.courseid = courses.id
     WHERE ue.timecreated > '.$fechaincial.' AND ue.timecreated < '.$fechafinal.' ) participantes_totales,
    (SELECT COUNT(DISTINCT ue.userid) AS total_approved
     FROM mdl_user_enrolments ue
              JOIN mdl_enrol e ON ue.enrolid = e.id
              JOIN mdl_course c ON e.courseid = courses.id
              JOIN mdl_grade_grades gg ON ue.userid = gg.userid
              JOIN mdl_grade_items gi ON gg.itemid = gi.id
              JOIN mdl_quiz q ON q.course = courses.id
     WHERE ue.timecreated > '.$fechaincial.' AND ue.timecreated < '.$fechafinal.'
       AND gi.courseid = courses.id
       AND gi.itemmodule = "quiz"
       AND gg.finalgrade >= gi.gradepass) aprobados,
    (SELECT COUNT(DISTINCT ue.userid) AS total_not_approved
     FROM mdl_user_enrolments ue
              JOIN mdl_enrol e ON ue.enrolid = e.id
              JOIN mdl_course c ON e.courseid = courses.id
              JOIN mdl_grade_grades gg ON ue.userid = gg.userid
              JOIN mdl_grade_items gi ON gg.itemid = gi.id
              JOIN mdl_quiz q ON q.course = courses.id
     WHERE ue.timecreated > '.$fechaincial.' AND ue.timecreated < '.$fechafinal.'
       AND gi.courseid = courses.id
       AND gi.itemmodule = "quiz"
       AND gg.finalgrade < gi.gradepass) no_aprobados,

    ((SELECT COUNT(DISTINCT ue.userid) AS total_participants
      FROM mdl_user_enrolments ue
               JOIN mdl_enrol e ON ue.enrolid = e.id
               JOIN mdl_course c ON e.courseid = courses.id
      WHERE ue.timecreated > '.$fechaincial.' AND ue.timecreated < '.$fechafinal.') - ((SELECT COUNT(DISTINCT ue.userid) AS total_approved
                                                            FROM mdl_user_enrolments ue
                                                                     JOIN mdl_enrol e ON ue.enrolid = e.id
                                                                     JOIN mdl_course c ON e.courseid = courses.id
                                                                     JOIN mdl_grade_grades gg ON ue.userid = gg.userid
                                                                     JOIN mdl_grade_items gi ON gg.itemid = gi.id
                                                                     JOIN mdl_quiz q ON q.course = courses.id
                                                            WHERE ue.timecreated > '.$fechaincial.' AND ue.timecreated < '.$fechafinal.'
                                                              AND gi.courseid = courses.id
                                                              AND gi.itemmodule = "quiz"
                                                              AND gg.finalgrade >= gi.gradepass)+(SELECT COUNT(DISTINCT ue.userid) AS total_not_approved
                                                                                                  FROM mdl_user_enrolments ue
                                                                                                           JOIN mdl_enrol e ON ue.enrolid = e.id
                                                                                                           JOIN mdl_course c ON e.courseid = courses.id
                                                                                                           JOIN mdl_grade_grades gg ON ue.userid = gg.userid
                                                                                                           JOIN mdl_grade_items gi ON gg.itemid = gi.id
                                                                                                           JOIN mdl_quiz q ON q.course = courses.id
                                                                                                  WHERE ue.timecreated > '.$fechaincial.' AND ue.timecreated < '.$fechafinal.'
                                                                                                    AND gi.courseid = courses.id
                                                                                                    AND gi.itemmodule = "quiz"
                                                                                                    AND gg.finalgrade < gi.gradepass)

         )) estudiantes_proceso,
    (SELECT COUNT(DISTINCT ci.userid) AS total_certificates
     FROM mdl_customcert_issues ci
              JOIN mdl_customcert c ON ci.customcertid = c.id AND c.course = courses.id
     WHERE ci.timecreated > '.$fechaincial.' AND ci.timecreated < '.$fechafinal.') constancias_emitidas
FROM mdl_course courses
WHERE  courses.id IN ('.$courses.')';
    }
       if($consulta != '') {
           $query = $DB->get_recordset_sql($consulta);
           //Válida si el reporte esta vacio
            if($query){
                    return $query;
            }else{
                if(sizeof($query) == 0){
                   return "not_data_show";
                }
                return "error_query";
                die();
            }
        }
        else{
            return "not_query_associated";
            die();
        }



}