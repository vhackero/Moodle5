<?php
require_once(__DIR__.'/../../../../config.php');
global $DB;

$ArrayES = array(
    'all_user_plataform' => "Usuarios del sitio(sin importar rol)",
    'all_users_active_n_days' => "Usuarios del sitio activos en n dias con rol asignado",
    'all_user_plataform_rol' => "Usuarios del sitio con rol asignado",
    'all_users_whithout_access' => "Usuarios del sitio(sin importar rol) que nunca accedieron",
    'all_partipants_whithout_access' => "Usuarios del sitio con rol asignado que nunca accedieron",
    'all_users_whith_one_access' => "Usuarios del sitio(sin importar rol) con al menos un acceso",
    'all_partipants_whith_one_access' => "Usuarios del sitio con rol con al menos un acceso",
    'all_partipants_in_n_courses_whithout_access' => "Usuarios del sitio con rol perteneciente a un curso que nunca accedieron(RL2019)",
    'all_partipants_in_n_courses_whithout_access_not_lista_espera' => "Usuarios del sitio con rol perteneciente a un curso que nunca accedieron sin contar lista de espera (CVL)",
    'all_partipants_active_one_course_n_days' => "Usuarios del sitio con rol perteneciente a n cursos activos de n días a la fecha(RL2019)",
    'all_participants_in_actual_category_and_old' => "Usuarios del sitio con rol inscritos en la categoría anterior y actual",
    'count_participants_per_groups' => "Conteo de los participantes por grupos",
    'count_participants_per_groups_types' => "Conteo de los participantes por estructura de grupos(CVL)",
    'report_all_data_with_course_and_group' => "Lista de usuarios con rol por curso,grupo y datos generales",
    'report_week_access' => "Reporte de usuarios con rol por curso,grupo y accesos",
    'report_status_acreditado' => "Reporte de usuarios con rol , curso, grupo y datos generales con estatus de acreditación en un curso(RL2019)",
    'report_access_n_course_with_rol' => "Reporte de usuarios con rol que ingresaron al menos una vez en n cursos(RL2019)",
    'report_access_with_rol' => "Reporte de usuarios con rol que se registraron (primer ingreso) de n días a n dias",
    'report_access_with_rol_in_course' => "Reporte de usuarios activos en curso de n días a n dias en n cursos",
    'report_paticipation_course' => "Reporte de participación en n cursos (Reporte trimestral)",
);
$html= "<option value=''>Selecciona el reporte a generar</option>";
$keys = array_keys($ArrayES);
for ($i=0; $i<= sizeof($keys)-1; $i++){
    $key =$keys[$i];
    $ArrayES[$keys[$i]];
    $html .= "<option value='" . $key =$keys[$i]."'>" . $ArrayES[$keys[$i]] . "</option>";
}
echo $html;

