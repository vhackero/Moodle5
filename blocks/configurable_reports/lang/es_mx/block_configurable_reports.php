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
 * Mexican Spanish language strings for Configurable Reports custom additions.
 *
 * @package    block_configurable_reports
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['exportcourse'] = 'Curso';
$string['exportevaluatedteacher'] = 'Docente evaluado';
$string['exportextractiondate'] = 'Fecha de extracción';
$string['exportinstitution'] = 'La institución';
$string['exportreportgraph'] = 'Gráfica del reporte';
$string['filter_required_to_run_report'] = 'Selecciona un filtro y haz clic en Aplicar para generar el reporte.';
$string['filteruserbyrolecourse'] = 'Usuario por grupo y rol (id de grupo)';
$string['filteruserbyrolecourse_option'] = '{$a->user} ({$a->group})';
$string['filteruserbyrolecourse_select'] = 'Usuario por grupo y rol - id de grupo ({$a})';
$string['filteruserbyrolecourse_summary'] = 'Filtra por id del grupo usando usuarios con el rol {$a} en el curso actual. Uso: %%FILTER_USERBYROLEGROUP:prefix_groups.id%%';
$string['filteruserbyrolecourse_summary_norole'] = 'Filtra por id del grupo usando usuarios con un rol configurado';
$string['userbyrolecourse'] = 'Usuario por grupo y rol (id de grupo)';
$string['filteruserbyrolegroupuser'] = 'Usuario por grupo y rol (id de usuario)';
$string['filteruserbyrolegroupuser_option'] = '{$a->user} ({$a->groups})';
$string['filteruserbyrolegroupuser_select'] = 'Usuario por grupo y rol - id de usuario ({$a})';
$string['filteruserbyrolegroupuser_summary'] = 'Filtra por id del usuario usando usuarios con el rol {$a} en al menos un grupo del curso actual. Uso: %%FILTER_USERBYROLEGROUPUSER:prefix_user.id%%';
$string['filteruserbyrolegroupuser_summary_norole'] = 'Filtra por id del usuario usando usuarios con un rol configurado en al menos un grupo';
$string['userbyrolegroupuser'] = 'Usuario por grupo y rol (id de usuario)';
