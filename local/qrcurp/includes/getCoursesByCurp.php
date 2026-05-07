<?php
require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/group/lib.php');

function local_qrcurp_parse_group_limits_for_courses($rawlimit): array {
    $config = ['default' => 0, 'rules' => []];
    $raw = trim((string)$rawlimit);
    if ($raw === '') {
        return $config;
    }
    if (ctype_digit($raw)) {
        $config['default'] = (int)$raw;
        return $config;
    }
    foreach (preg_split('/[\r\n;]+/', $raw) as $part) {
        $part = trim($part);
        if ($part === '' || strpos($part, ':') === false) {
            continue;
        }
        [$needle, $limit] = array_map('trim', explode(':', $part, 2));
        if (!ctype_digit($limit)) {
            continue;
        }
        if ($needle === '*') {
            $config['default'] = (int)$limit;
            continue;
        }
        if ($needle !== '') {
            $config['rules'][] = ['needle' => core_text::strtolower($needle), 'limit' => (int)$limit];
        }
    }
    return $config;
}

function local_qrcurp_group_limit_for_name_for_courses(string $groupname, array $config): int {
    $name = core_text::strtolower($groupname);
    foreach ($config['rules'] as $rule) {
        if (core_text::strpos($name, $rule['needle']) !== false) {
            return (int)$rule['limit'];
        }
    }
    return (int)($config['default'] ?? 0);
}

function local_qrcurp_course_has_available_group(int $courseid, int $rolstudent, array $limitsconfig, int $waitingenabled, string $waitingname): bool {
    global $DB, $CFG;
    $groups = $DB->get_records('groups', ['courseid' => $courseid], 'name ASC');
    foreach ($groups as $group) {
        $groupname = (string)$group->name;
        if ($waitingenabled === 1 && $waitingname !== '' && $groupname === $waitingname) {
            continue;
        }
        $limit = local_qrcurp_group_limit_for_name_for_courses($groupname, $limitsconfig);
        if ($limit <= 0) {
            return true;
        }
        $sql = "SELECT COUNT(DISTINCT u.id)
                  FROM {$CFG->prefix}role_assignments ra
                  JOIN {$CFG->prefix}context ctx ON ra.contextid = ctx.id
                  JOIN {$CFG->prefix}user u ON u.id = ra.userid
                  JOIN {$CFG->prefix}groups_members gm ON gm.userid = u.id
                 WHERE ra.roleid = :rolstudent
                   AND ctx.instanceid = :courseid
                   AND gm.groupid = :groupid";
        $count = (int)$DB->count_records_sql($sql, ['rolstudent' => $rolstudent, 'courseid' => $courseid, 'groupid' => (int)$group->id]);
        if ($count < $limit) {
            return true;
        }
    }
    return false;
}

$curp = trim((string)($_POST['curp'] ?? ''));
$idcategoria = (int)get_config('local_qrcurp', 'enrolmoreperiodcategoryid');
if ($idcategoria <= 0) {
    $idcategoria = 2;
}
$limitedecursos = 2;
$existeEnPeridoActual = 0;
$pertencelista = 0;
$html = '';

if ($curp === '') {
    echo "|0||0|0";
    exit;
}

$field = $DB->get_record('user_info_field', ['shortname' => 'curp'], 'id');
if (!$field) {
    echo "|0||0|0";
    exit;
}

$sqlcurp = "SELECT uid.userid
              FROM {user_info_data} uid
             WHERE uid.fieldid = :fieldid
               AND " . $DB->sql_compare_text('uid.data') . " = " . $DB->sql_compare_text(':curp');
$usercurp = $DB->get_record_sql($sqlcurp, ['fieldid' => (int)$field->id, 'curp' => $curp]);
if (!$usercurp) {
    echo "|0||0|0";
    exit;
}

$iduser = (int)$usercurp->userid;
$user = $DB->get_record('user', ['id' => $iduser], 'id,email');
$email = $user ? (string)$user->email : '';

$coursesenrolled = enrol_get_all_users_courses($iduser);
$langcount = [];
foreach ($coursesenrolled as $course) {
    if ((int)$course->category === $idcategoria) {
        $existeEnPeridoActual = 1;
    }
    $groupsbycourse = groups_get_user_groups((int)$course->id, $iduser);
    foreach ($groupsbycourse as $groupids) {
        foreach ($groupids as $gid) {
            $group = $DB->get_record('groups', ['id' => (int)$gid], 'id,courseid,name');
            if (!$group) {
                continue;
            }
            $waitname = (string)get_config('local_qrcurp', 'namegroupespera');
            if ($waitname !== '' && core_text::strpos(core_text::strtolower($group->name), core_text::strtolower($waitname)) !== false) {
                $pertencelista = 1;
                continue;
            }
            $key = core_text::strtolower(trim((string)$course->fullname));
            $langcount[$key] = ($langcount[$key] ?? 0) + 1;
        }
    }
}

$rolstudent = (int)get_config('local_qrcurp', 'rolstudent');
$waitingenabled = (int)get_config('local_qrcurp', 'haygroupespera');
$waitingname = (string)get_config('local_qrcurp', 'namegroupespera');
$limitsconfig = local_qrcurp_parse_group_limits_for_courses(get_config('local_qrcurp', 'limitegroup'));

$categorycourses = $DB->get_records('course', ['category' => $idcategoria], 'fullname ASC', 'id,fullname,category');
$html = "<option value=''>" . get_string('selectoption', 'local_qrcurp') . "</option>";

foreach ($categorycourses as $course) {
    $key = core_text::strtolower(trim((string)$course->fullname));
    if (($langcount[$key] ?? 0) >= $limitedecursos) {
        continue;
    }
    if (!local_qrcurp_course_has_available_group((int)$course->id, $rolstudent, $limitsconfig, $waitingenabled, $waitingname)) {
        continue;
    }
    $html .= "<option value='" . (int)$course->id . "'>" . format_string($course->fullname) . "</option>";
}

echo $html;
echo "|".$iduser."|".$email."|".$pertencelista."|".$existeEnPeridoActual;
