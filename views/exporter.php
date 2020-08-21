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
		$attachment_count = sizeof(getFiles($forum->id));
		echo '<option value ="' . $forum->id . '">' . $forum->name . ' (' . $attachment_count . " " . get_string('files', 'local_forum_attachment_collector') . ')</option>';
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

function getFiles($forumid){
	global $DB;

	$sql = "SELECT mf2.filename, mfd.id, mf.name AS forumname, mc2.fullname as coursename, mfd.name AS discussion, " .
	"mf2.contextid, mf2.component, mf2.filearea, mf2.itemid, mf2.filepath, mf2.author " .
	"FROM {forum_discussions} mfd " .
	"INNER JOIN {course_modules} mcm ON (mfd.forum = mcm.instance) " .
	"INNER JOIN {context} mc ON (mcm.id = mc.instanceid) " .
	"INNER JOIN {files} mf2 ON (mf2.contextid = mc.id) " .
	"INNER JOIN {forum} mf ON (mfd.forum = mf.id) " .
	"INNER JOIN {course} mc2 ON (mf.course = mc2.id) " .
	"WHERE mfd.id = :forum_id AND mf2.filename <> '.'";

	$files = $DB->get_records_sql($sql, array('forum_id'=>$forumid));

	return $files;
}

function createDownloadPackage(){
	global $CFG;
	$files = getFiles($_POST['selectForum']);
	$fs = get_file_storage();
	$zip = array();

	$coursename = "";

	foreach ($files as $attachment){
		$attachment_file = $fs->get_file(
		$attachment->contextid,
		$attachment->component,
		$attachment->filearea,
		$attachment->itemid,
		$attachment->filepath,
		$attachment->filename);
		$coursename = $attachment->coursename;
		$ext = end(explode('.', $attachment->filename));
		$pathname = $attachment->forumname . "_Forum/" . $attachment->discussion . "/" . $attachment->filename . "_by_" . $attachment->author . "." . $ext;
		$zip[$pathname] = $attachment_file;
	}

		$tempzip = tempnam($CFG->tempdir . '/', 'forum_attachments_');
		$zipper = new zip_packer();

		if ($zipper->archive_to_pathname($zip, $tempzip)) {
			send_temp_file($tempzip, $coursename . "_attachments.zip");
		}
	}
?>
