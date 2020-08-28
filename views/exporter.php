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
if(!has_capability('moodle/course:manageactivities', $context)) return;

$pageurl = new moodle_url('/local/forum_attachment_collector_exporter/views/exportpage.php');
$PAGE->set_url($pageurl);

$PAGE->set_title(get_string('forum_attachment_collector_exporter', 'local_forum_attachment_collector'));
$PAGE->set_heading(get_string('forum_attachment_collector_exporter', 'local_forum_attachment_collector'));
$PAGE->set_pagelayout('standard');
require_capability('local/forum_attachment_collector:viewexporter', $context);

$submitlink = $CFG->wwwroot . "/local/forum_attachment_collector/views/exporter.php";

$forums = "";


if(!isset($_GET['selectForum'])){
	echo $OUTPUT->header();
	echo get_string('select_course', 'local_forum_attachment_collector') .':<br>';
	echo '<form class="forum-attachment-export-form" action= "' . $submitlink . '" method="GET">';
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

if(isset($_GET['selectCourse']) && !isset($_GET['selectForum'])){
	$all_forums = getForums();
	echo get_string('select_forum', 'local_forum_attachment_collector') .':<br>';
	echo '<form class="forum-attachment-export-form" action= "' . $submitlink . '" method="GET">';
	echo '<select name = "selectForum">';
	foreach ($all_forums as $forum){
		$attachment_count = sizeof(getFiles($forum->id));
		if($attachment_count > 0 && $forum->course == $_GET['selectCourse']) echo '<option value ="' . $forum->id . '">' . $forum->name . ' (' . $attachment_count . " " . get_string('files', 'local_forum_attachment_collector') . ')</option>';
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

	$sql = "select * from {forum};";
	$forums = $DB->get_records_sql($sql);

	return $forums;
}

function getFiles($forumid){
	global $DB;

	$sql = "select mf.*, mfd.name as discussion, mf2.name as forumname, mc2.fullname as coursename, mc2.id as courseid
	from {files} mf
	inner join {context} mc on(mf.contextid = mc.id)
	inner join {course_modules} mcm on(mc.instanceid = mcm.id)
	inner join {forum_discussions} mfd on(mcm.instance = mfd.forum)
	inner join {forum} mf2 on(mf2.id = mcm.instance)
	inner join {course} mc2 on(mc2.id = mf2.course)
	where mf.filename <> '.' and mf.filearea = 'attachment' and mcm.instance = :forum_id";

	$files = $DB->get_records_sql($sql, array('forum_id'=>$forumid));

	return $files;
}

function createDownloadPackage(){
	global $CFG;
	$files = getFiles($_GET['selectForum']);
	$fs = get_file_storage();
	$zip = array();

	$coursename = "";
	$forumname = "";

	foreach ($files as $attachment){
		$attachment_file = $fs->get_file(
		$attachment->contextid,
		$attachment->component,
		$attachment->filearea,
		$attachment->itemid,
		$attachment->filepath,
		$attachment->filename);
		$coursename = $attachment->coursename;
		$forumname =  $attachment->forumname;
		$ext = end(explode('.', $attachment->filename));
		$pathname = $attachment->forumname . "_Forum/" . $attachment->filename . "_by_" . $attachment->author . "." . $ext;
		$zip[$pathname] = $attachment_file;
	}

		$tempzip = tempnam($CFG->tempdir . '/', 'forum_attachments_');
		$zipper = new zip_packer();

		if ($zipper->archive_to_pathname($zip, $tempzip)) {
			send_temp_file($tempzip, $coursename . "_" . $forumname . "_attachments.zip");
		}
	}
?>
