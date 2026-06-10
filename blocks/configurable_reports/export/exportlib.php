<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Common export helpers.
 *
 * @package    block_configurable_reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Clean a value before adding it to spreadsheet exports.
 *
 * @param mixed $value
 * @return string
 */
function block_configurable_reports_export_clean_cell($value): string {
    return str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br(format_string($value)))));
}

/**
 * Build export metadata rows.
 *
 * @param object $report
 * @return array
 */
function block_configurable_reports_export_metadata_rows(object $report): array {
    global $COURSE;

    $config = $report->config ?? null;
    $institution = $config->institution ?? '';
    $teacher = block_configurable_reports_export_get_evaluated_teacher($report);

    return [
        [get_string('exportinstitution', 'block_configurable_reports') . ': ' . $institution],
        [get_string('exportcourse', 'block_configurable_reports') . ': ' . format_string($COURSE->fullname)],
        [get_string('exportevaluatedteacher', 'block_configurable_reports') . ': ' . $teacher],
        [get_string('exportextractiondate', 'block_configurable_reports') . ': ' . userdate(time())],
        [],
    ];
}

/**
 * Find the evaluated teacher from active custom filters.
 *
 * @param object $report
 * @return string
 */
function block_configurable_reports_export_get_evaluated_teacher(object $report): string {
    global $COURSE, $DB;

    if (empty($report->config->components)) {
        return '';
    }

    $components = cr_unserialize($report->config->components);
    $filters = $components['filters']['elements'] ?? [];
    $teachers = [];

    foreach ($filters as $filter) {
        $pluginname = $filter['pluginname'] ?? '';
        $formdata = $filter['formdata'] ?? null;
        $roleid = (is_object($formdata) && !empty($formdata->roleid)) ? (int) $formdata->roleid : 0;

        if (!$roleid) {
            continue;
        }

        if ($pluginname === 'userbyrolegroupuser') {
            $userid = optional_param('filter_userbyrolegroupuser_' . $roleid, 0, PARAM_INT);
            if ($userid && $user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0])) {
                $teachers[$userid] = fullname($user);
            }
            continue;
        }

        if ($pluginname === 'userbyrolecourse') {
            $groupid = optional_param('filter_userbyrolecourse_' . $roleid, 0, PARAM_INT);
            if (!$groupid) {
                continue;
            }

            $sql = "SELECT DISTINCT u.id,
                           u.firstname,
                           u.lastname,
                           u.firstnamephonetic,
                           u.lastnamephonetic,
                           u.middlename,
                           u.alternatename
                      FROM {role_assignments} ra
                      JOIN {context} ctx ON ctx.id = ra.contextid
                           AND ctx.contextlevel = :contextlevel
                           AND ctx.instanceid = :courseid
                      JOIN {user} u ON u.id = ra.userid
                      JOIN {groups_members} gm ON gm.userid = u.id
                           AND gm.groupid = :groupid
                     WHERE ra.roleid = :roleid
                           AND u.deleted = 0
                  ORDER BY u.lastname ASC, u.firstname ASC";

            $params = [
                'contextlevel' => CONTEXT_COURSE,
                'courseid' => $COURSE->id,
                'groupid' => $groupid,
                'roleid' => $roleid,
            ];

            foreach ($DB->get_records_sql($sql, $params) as $user) {
                $teachers[$user->id] = fullname($user);
            }
        }
    }

    return implode(', ', $teachers);
}

/**
 * Get the first graph URL for an exported report.
 *
 * @param object $report
 * @return string
 */
function block_configurable_reports_export_get_graph_url(object $report): string {
    if (empty($report->graphs) || !is_array($report->graphs)) {
        return '';
    }

    return html_entity_decode(reset($report->graphs));
}

/**
 * Download a graph URL to a temporary PNG file using the current Moodle session cookie.
 *
 * @param string $url
 * @return string
 */
function block_configurable_reports_export_download_graph(string $url): string {
    global $CFG;

    if (empty($url)) {
        return '';
    }

    require_once($CFG->libdir . '/filelib.php');

    $cookies = [];
    foreach ($_COOKIE as $name => $value) {
        $cookies[] = rawurlencode($name) . '=' . rawurlencode($value);
    }

    $headers = [];
    if (!empty($cookies)) {
        $headers[] = 'Cookie: ' . implode('; ', $cookies);
    }

    $content = block_configurable_reports_export_render_graph_locally($url);
    if (empty($content) || substr($content, 0, 8) !== "\x89PNG\x0d\x0a\x1a\x0a") {
        foreach (block_configurable_reports_export_graph_url_candidates($url) as $candidateurl) {
            $content = download_file_content($candidateurl, $headers, null, false, 60, 20, false);
            if (!empty($content) && substr($content, 0, 8) === "\x89PNG\x0d\x0a\x1a\x0a") {
                break;
            }
        }
    }

    if (empty($content) || substr($content, 0, 8) !== "\x89PNG\x0d\x0a\x1a\x0a") {
        return '';
    }

    $tempfile = make_temp_directory('block_configurable_reports') . '/' . uniqid('graph_', true) . '.png';
    file_put_contents($tempfile, $content);

    return $tempfile;
}

/**
 * Render a configurable reports graph script in-process and capture its PNG.
 *
 * This avoids depending on browser-facing wwwroot, container port mappings, or
 * a second HTTP request carrying the Moodle session cookie.
 *
 * @param string $url
 * @return string
 */
function block_configurable_reports_export_render_graph_locally(string $url): string {
    global $CFG;

    $parts = parse_url($url);
    if (empty($parts['path']) || empty($parts['query'])) {
        return '';
    }

    $path = ltrim($parts['path'], '/');
    if (!preg_match('#^blocks/configurable_reports/components/plot/(bar|line|pie)/graph\.php$#', $path)) {
        return '';
    }

    $script = $CFG->dirroot . '/' . $path;
    if (!file_exists($script)) {
        return '';
    }

    parse_str($parts['query'], $queryparams);

    if (strpos($path, '/pie/graph.php') !== false) {
        return block_configurable_reports_export_render_pie_graph($queryparams);
    }

    $oldget = $_GET;
    $oldpost = $_POST;
    $oldrequest = $_REQUEST;

    $_GET = $queryparams;
    $_POST = [];
    $_REQUEST = $queryparams;

    ob_start();
    try {
        include($script);
        $content = ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        $content = '';
    }

    $_GET = $oldget;
    $_POST = $oldpost;
    $_REQUEST = $oldrequest;

    if (function_exists('header_remove') && !headers_sent()) {
        header_remove('Content-Type');
    }

    return $content;
}

/**
 * Render a pie graph directly from the graph URL query parameters.
 *
 * @param array $queryparams
 * @return string PNG binary content, or empty string on failure.
 */
function block_configurable_reports_export_render_pie_graph(array $queryparams): string {
    global $CFG;

    if (empty($queryparams['serie0']) || empty($queryparams['serie1'])) {
        return '';
    }

    require_once($CFG->dirroot . '/blocks/configurable_reports/lib/pChart/pData.class.php');
    require_once($CFG->dirroot . '/blocks/configurable_reports/lib/pChart/pChart.class.php');

    $labels = explode(',', base64_decode($queryparams['serie0']));
    $values = explode(',', base64_decode($queryparams['serie1']));
    if (empty($labels) || empty($values)) {
        return '';
    }

    $dataset = new pData();
    $dataset->AddPoint($values, 'Serie1');
    $dataset->AddPoint(array_map('strip_tags', $labels), 'Serie2');
    $dataset->AddAllSeries();
    $dataset->SetAbsciseLabelSerie('Serie2');

    $chart = new pChart(450, 200 + (count($labels) * 10));
    $chart->drawFilledRoundedRectangle(7, 7, 293, 193, 5, 240, 240, 240);
    $chart->drawRoundedRectangle(5, 5, 295, 195, 5, 230, 230, 230);
    $chart->createColorGradientPalette(195, 204, 56, 223, 110, 41, 5);

    if (!empty($queryparams['colorpalette'])) {
        $colors = explode(',', base64_decode($queryparams['colorpalette']));
        foreach ($colors as $index => $color) {
            if (empty($color)) {
                continue;
            }
            $rgb = explode('|', $color);
            if (count($rgb) === 3) {
                $chart->Palette[$index] = ['R' => (int) $rgb[0], 'G' => (int) $rgb[1], 'B' => (int) $rgb[2]];
            }
        }
    }

    $chart->setFontProperties($CFG->dirroot . '/blocks/configurable_reports/lib/Fonts/tahoma.ttf', 8);
    $chart->AntialiasQuality = 0;
    $chart->drawPieGraph($dataset->GetData(), $dataset->GetDataDescription(), 150, 90, 110, PIE_PERCENTAGE, true, 50, 20, 5);
    $chart->drawPieLegend(300, 15, $dataset->GetData(), $dataset->GetDataDescription(), 250, 250, 250);

    $tempfile = make_temp_directory('block_configurable_reports') . '/' . uniqid('pie_graph_', true) . '.png';
    $chart->Render($tempfile);
    $content = file_exists($tempfile) ? file_get_contents($tempfile) : '';
    if (file_exists($tempfile)) {
        unlink($tempfile);
    }

    return $content;
}

/**
 * Build graph URL candidates for server-side downloads.
 *
 * Browser-facing wwwroot may use a host port that is not reachable from inside
 * the web container. Try the public URL first, then a local web-server URL.
 *
 * @param string $url
 * @return array
 */
function block_configurable_reports_export_graph_url_candidates(string $url): array {
    $urls = [$url];
    $parts = parse_url($url);

    if (empty($parts['scheme']) || empty($parts['host']) || empty($parts['path'])) {
        return $urls;
    }

    $host = strtolower($parts['host']);
    $port = $parts['port'] ?? null;
    if (in_array($host, ['localhost', '127.0.0.1', '::1'], true) && $port && (int) $port !== 80) {
        $localurl = $parts['scheme'] . '://127.0.0.1' . $parts['path'];
        if (!empty($parts['query'])) {
            $localurl .= '?' . $parts['query'];
        }
        $urls[] = $localurl;
    }

    return array_values(array_unique($urls));
}
