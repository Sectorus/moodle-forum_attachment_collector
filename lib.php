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
 * Version details.
 *
 * @package   forum_attachment_collector
 * @copyright 2020 Stephan Lorbek
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function local_forum_attachment_collector_extend_settings_navigation(settings_navigation $nav, context $context){
  global $PAGE, $CFG, $COURSE, $DB;

if(!has_capability('moodle/course:manageactivities', $context)) return;
if ($PAGE->context->contextlevel == CONTEXT_MODULE && $PAGE->cm->modname === 'forum') {
        $a_collect = navigation_node::create(get_string('attachment_collector', 'local_forum_attachment_collector'));
        $a_collect->key = 'forum2pdf';
        $ctx = context_course::instance($COURSE->id);
        
        $sql = "SELECT id FROM mdl_course_modules WHERE instance = :id";
        $files = $DB->get_records_sql($sql, array('id'=> $PAGE->cm->instance));
        //
        $a_collect->action = new moodle_url('/local/forum_attachment_collector/views/exporter.php', array('selectCourse' => $COURSE->id, 'selectForum' => $PAGE->cm->instance));

        $modulesettings = $nav->get('modulesettings');
        $modulesettings->add_node($a_collect);
        //echo "<pre>"; var_dump($pdflink); echo "</pre>";
    }
}
