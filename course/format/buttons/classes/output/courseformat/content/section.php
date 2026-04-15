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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     format_buttons
 * @category    upgrade
 * @copyright   2023 Jhon Rangel <jrangelardila@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_buttons\output\courseformat\content;

defined('MOODLE_INTERNAL') || die();

use core_courseformat\base as course_format;
use core_courseformat\output\local\content\section as section_base;
use format_buttons\classes\output\courseformat\content\section\controlmenu;
use renderer_base;
use section_info;
use stdClass;

class section extends section_base
{

    /**
     * constructor
     *
     * @param course_format $format
     * @param section_info $section
     */
    public function __construct(course_format $format, section_info $section)
    {
        parent::__construct($format, $section);

        $this->headerclass = 'format_buttons\\classes\\output\\courseformat\\content\\section\\header';

    }

    /**
     * Template name
     *
     * @param renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string
    {
        return 'format_buttons/local/content/section';
    }

    /**
     * Export for template
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output): stdClass
    {
        $data = parent::export_for_template($output);

        $controlmenu = new controlmenu($this->format, $this->section);
        $data->controlmenu = $controlmenu->export_for_template($output);

        $showtitles = (int)($this->format->get_course()->title_section_view ?? 1) === 1;
        $sectionnum = (int)$this->section->section;
        $data->showscheduletitle = $showtitles && $sectionnum > 1;
        if ($data->showscheduletitle) {
            $data->sectiontitlename = format_string($this->format->get_section_name($this->section));
            $data->sectionweeklyrange = $this->get_weekly_range_label($sectionnum);
        }

        return $data;
    }

    /**
     * Build weekly date range label for a section.
     * Section 2 is week 1, section 3 is week 2, etc.
     *
     * @param int $sectionnum
     * @return string
     */
    private function get_weekly_range_label(int $sectionnum): string {
        $course = $this->format->get_course();
        if (empty($course->startdate)) {
            return '';
        }

        $weekindex = max(0, $sectionnum - 2);
        $start = strtotime("+{$weekindex} week", (int)$course->startdate);
        $end = strtotime('+6 day', $start);

        $startlabel = trim(preg_replace('/\s+/', ' ', userdate($start, '%A %e de %B de %Y')));
        $endlabel = trim(preg_replace('/\s+/', ' ', userdate($end, '%A %e de %B de %Y')));

        return "del {$startlabel} al {$endlabel}";
    }


}
