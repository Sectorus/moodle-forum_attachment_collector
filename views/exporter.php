<?php
/**
* Local plugin forum_attachment_collector export page
*
* @package   forum_attachment_collector
* @copyright 2020 Stephan Lorbek
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require('../../../config.php');
require_once($CFG->libdir.'/filelib.php');
core_php_time_limit::raise();

$context = context_system::instance();

$pageurl = new moodle_url('/local/forum_attachment_collector_exporter/views/exportpage.php');
$PAGE->set_url($pageurl);

$PAGE->set_title(get_string('forum_attachment_collector_exporter', 'local_forum_attachment_collector'));
$PAGE->set_heading(get_string('forum_attachment_collector_exporter', 'local_forum_attachment_collector'));
$PAGE->set_pagelayout('standard');
require_capability('local/forum_attachment_collector:viewexporter', $context);

$submitlink = $CFG->wwwroot . "/local/forum_attachment_collector/views/exporter.php";

$forums = "";


if(!isset($_POST['selectForum'])){
	echo $OUTPUT->header();
	echo get_string('select_course', 'local_forum_attachment_collector') .':<br>';
	echo '<form class="forum-attachment-export-form" action= "' . $submitlink . '" method="POST">';
	echo '<select name = "selectCourse">';

	$forum_courses = getCourses();
	foreach ($forum_courses as $course){
		echo '<option value ="' . $course->course . '">' . $course->fullname . '</option>';
	}

	echo '</select>';
	echo '<input type = "submit" name="submit" class="btn-download" value="Lade Kursforen" />';
	echo '</form>';
}
else {
	createDownloadPackage();
}

if(isset($_POST['selectCourse']) && !isset($_POST['selectForum'])){
	$all_forums = getForums();
	echo get_string('select_forum', 'local_forum_attachment_collector') .':<br>';
	echo '<form class="forum-attachment-export-form" action= "' . $submitlink . '" method="POST">';
	echo '<select name = "selectForum">';
	foreach ($all_forums as $forum){
		echo '<option value ="' . $forum->id . '">' . $forum->name . ' (' . context_course::instance($_POST['selectCourse'])->id . ')</option>';
	}
	echo '<input type = "submit" name="submit" class="btn-download" value="';
	echo get_string('expbtn', 'local_forum_attachment_collector') . '" />';
	echo '</select></form>';
	echo $OUTPUT->footer();
}
else {
	echo $OUTPUT->footer();
}

function getCourses(){
	global $DB;

	$sql = "select distinct fullname, course from {course} c inner join {forum_discussions} fd on c.id = fd.course;";
	$courses = $DB->get_records_sql($sql);

	return $courses;
}

function getForums(){
	global $DB;

	$sql = "select distinct * from {forum_discussions};";
	$courses = $DB->get_records_sql($sql);

	return $courses;
}

function getFiles(){
	global $DB;
	$sql = "select distinct mfd.id as forumid, mf2.* " .
	"from {forum_discussions} mfd inner join {course_modules} mcm on (mfd.forum = mcm.instance) " .
	"inner join {context} mc on (mcm.id = mc.instanceid) " .
	"inner join {files} mf2 on (mf2.contextid = mc.id) " .
	"where mf2.filearea = 'attachment' and not mf2.filename = '.';";
	$files = $DB->get_records_sql($sql);

	return $files;
}

function createDownloadPackage(){
	global $CFG;
	$files = getFiles();
	$fs = get_file_storage();
	$zip = array();

	foreach ($files as $attachment){
		if(strcmp($attachment->forumid, $_POST['selectForum'])){
			$attachment_file = $fs->get_file(
				$attachment->contextid,
				$attachment->component,
				$attachment->filearea,
				$attachment->itemid,
				$attachment->filepath,
				$attachment->filename);
				$zip[$attachment->filename] = $attachment_file;
			}
		}

		$tempzip = tempnam($CFG->tempdir . '/', 'forum_attachments_');
		$zipper = new zip_packer();

		if ($zipper->archive_to_pathname($zip, $tempzip)) {
			send_temp_file($tempzip, "course_attachments.zip");
		}
	}
?>
