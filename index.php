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
 *
*
* @package    local
* @subpackage coursehub
* @copyright  2017	Mark Michaelsen (mmichaelsen678@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(dirname(dirname(dirname(__FILE__))) . "/config.php");
require_once($CFG->dirroot . '/local/coursehub/forms/assign_form.php');
global $CFG, $DB, $OUTPUT, $PAGE, $USER;

// User must be logged in.
require_login();
if (isguestuser()) {
	die();
}

$nombrecortocurso = optional_param('shortname', null, PARAM_TEXT);
$action = optional_param('action', null, PARAM_TEXT);
$insert = optional_param('insert', "", PARAM_TEXT);

$context = context_system::instance();

if($action == null){
echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
echo '<link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
$url = new moodle_url("/local/coursehub/index.php");
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout("standard");
$PAGE->set_title(get_string("page_title", "local_coursehub"));
$PAGE->set_heading(get_string("page_heading", "local_coursehub"));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin ( 'ui' );
$PAGE->requires->jquery_plugin ( 'ui-css' );

echo $OUTPUT->header();

echo html_writer::tag('h3','Notificaciones de foro');
if ($insert == "success") {
	echo $OUTPUT->notification("Mensajes enviados correctamente", "notifysuccess");
}
echo html_writer::start_tag('div',array('style' => 'width:100%; height: 210px; overflow:auto;'));

$usercoursesquery = "SELECT c.id, c.shortname
	FROM mdl_user u
	JOIN mdl_user_enrolments ue ON ue.userid = u.id
	JOIN mdl_enrol e ON e.id = ue.enrolid
	JOIN mdl_role_assignments ra ON ra.userid = u.id
	JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
	JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
	JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
	JOIN mdl_course_categories cc ON c.category = cc.id";

$usercourses = $DB->get_records_sql($usercoursesquery);

echo '<ul class="collection with-header">';
foreach ($usercourses as $usercur){
	$forumquery = "SELECT fp.id, u.username, fp.message, cm.id as cmid,f.timemodified
		FROM mdl_forum_discussions fd
		INNER JOIN mdl_forum_posts fp ON fp.discussion = fd.id
		INNER JOIN mdl_user u ON u.id = fp.userid
		INNER JOIN mdl_forum f ON f.id = fd.forum
		INNER JOIN mdl_course_modules cm ON cm.module = 9  AND cm.instance = f.id 
		WHERE fd.course = ".$usercur->id."
		ORDER BY modified";
	
	$forumdiscussions = $DB->get_records_sql($forumquery);
	foreach ($forumdiscussions as $comment){
		$url = new moodle_url('/mod/forum/view.php', array("id"=>$comment->cmid));
		echo '<li class="collection-item"><div>'.$comment->username." escribio en el foro del curso: ".$usercur->shortname.' a las: '.gmdate("H:i:s Y-m-d ", $comment->timemodified).'<a href="'.$url.'" class="secondary-content"><i class="material-icons">send</i></a></div></li>';
	}
}
echo '</ul>';

echo html_writer::end_tag('div');

echo html_writer::tag('h3','Notificaciones de tarea');
echo html_writer::start_tag('div',array('style' => 'width:100%; height: 210px; overflow:auto;'));

echo '<ul class="collection with-header">';
foreach ($usercourses as $usercur){
	$forumquery = "SELECT mas.id, ma.course, ma.name, ma.intro, mas.userid, u.username, mas.timemodified, ma.course as cmid FROM `mdl_assign` ma
		INNER JOIN mdl_assign_submission mas
		INNER JOIN mdl_user u
		WHERE ma.id = mas.assignment AND u.id = mas.userid AND ma.course = ".$usercur->id."
		ORDER BY mas.timemodified";
	
	$forumdiscussions = $DB->get_records_sql($forumquery);
	foreach ($forumdiscussions as $comment){
		$url = new moodle_url('/course/view.php', array("id"=>$comment->cmid));
		echo '<li class="collection-item"><div>'.$comment->username." observo la tarea: ".$comment->name." curso: ".$usercur->shortname.'<a href="'.$url.'" class="secondary-content"><i class="material-icons">send</i></a></div></li>';
	}
}
echo '</ul>';

echo html_writer::end_tag('div');
	echo html_writer::tag('h3','Acciones globales');
	echo html_writer::start_tag('div',array('style' => 'width:100%; height: 10%;'));
	$table = new html_table("p");
	$table->size = array(
			"25%",
			"25%",
			"25%",
			"25%"
	);
	$urlforos = new moodle_url('/local/coursehub/message.php');
	$urlassign = new moodle_url('/local/coursehub/index.php', array("action"=>"assign"));
	$urlemarking = new moodle_url('/local/coursehub/index.php', array("action"=>"emarking"));
	$table->data = array(
			array($OUTPUT->single_button($urlforos, 'Escribir en foros'),$OUTPUT->single_button($urlemarking, 'Agregar eMarkings'),$OUTPUT->single_button($urlassign, 'Agregar Tareas'))
	);
	echo html_writer::table($table);
	echo html_writer::end_tag('div');
	echo html_writer::tag('h3','Mis cursos');
	
	$coursetable = new html_table();
	$coursetable->size = array(
			"33%",
			"34%",
			"33%",
	);
	
	if($nombrecortocurso != null){
		$usercourseparam="'%2220-S-$nombrecortocurso%'";
		$usercoursesql = "SELECT c.*
		FROM mdl_user u
		JOIN mdl_user_enrolments ue ON ue.userid = u.id
		JOIN mdl_enrol e ON e.id = ue.enrolid
		JOIN mdl_role_assignments ra ON ra.userid = u.id
		JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
		JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
		JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
		JOIN mdl_course_categories cc ON c.category = cc.id
		WHERE c.shortname like $usercourseparam AND c.shortname like '%-2-2017'";
		$usercourse = $DB->get_records_sql($usercoursesql);
		$courseidarray = array ();
		foreach ( $usercourse as $courses ) {
			// Only visible courses
			if($courses->visible == 1){
				$courseidarray [] = $courses->id;
			}else{
				// Remove invisible courses
				unset($usercourse[$key]);
			}
		}
		
		
		$data = array();
		$data[0][0] = '';
		$data[0][1] = '';
		$data[0][2] = '';
		$count = 0;
		$row = 0;
		foreach ( $usercourse as $courses ) {
			
			$fullname = $courses->fullname;
			$courseid = $courses->id;
			$shortname = $courses->shortname;
			
			$url = new moodle_url("/course/view.php", array("id" => "$courseid"));
			$html = '<a href='.$url.'><button type="button" class="btn btn-info btn-lg" style="white-space: normal; width: 90%; height: 90%; border: 1px solid lightgray; background: #F0F0F0;" courseid="' . $courseid . '" fullname="' . $fullname . '" moodleid="'.$USER->id.'" component="button">';
			$html .= '<p class="name" align="left" style="position: relative; height: 3em; overflow: hidden; color: black; font-weight: bold; text-decoration: none; font-size:13px; word-wrap: initial;" courseid="' . $courseid . '" moodleid="'.$USER->id.'" component="button">
					' . $fullname . '</p>';
			$html .= '</button></a>';
			$data[$row][$count] = $html;
			$count++;
			if($count>2){
				$count = 0;
				$row++;
				$data[$row][0] = '';
				$data[$row][1] = '';
				$data[$row][2] = '';
			}
		}
		
		$coursetable->data = $data;
		echo html_writer::table($coursetable);
	}else{
		$usercoursesql = "SELECT c.id, c.shortname
		FROM mdl_user u
		JOIN mdl_user_enrolments ue ON ue.userid = u.id
		JOIN mdl_enrol e ON e.id = ue.enrolid
		JOIN mdl_role_assignments ra ON ra.userid = u.id
		JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
		JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
		JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
		JOIN mdl_course_categories cc ON c.category = cc.id";
		$usercourse = $DB->get_records_sql($usercoursesql);
		$courseidarray = array ();
		foreach ( $usercourse as $courses ) {
			$course = explode("-",$courses->shortname);
			
			if(!in_array($course[2], $courseidarray)){
				// Only visible courses
				$courseidarray [] = $course[2];
			}
			
		}
		
		
		$data = array();
		$data[0][0] = '';
		$data[0][1] = '';
		$data[0][2] = '';
		$count = 0;
		$row = 0;
		foreach ( $courseidarray as $courses ) {
			$fullname = $courses->fullname;
			$courseid = $courses->id;
			$shortname = $courses->shortname;
			$courseshort = explode("-",$courses);
			$url = new moodle_url("/local/coursehub/index.php", array("shortname" => "$courses"));
			$html = '<a href='.$url.'><button type="button" class="btn btn-info btn-lg" style="white-space: normal; width: 90%; height: 90%; border: 1px solid lightgray; background: #F0F0F0;" courseid="' . $courseid . '" fullname="' . $fullname . '" moodleid="'.$USER->id.'" component="button">';
			$html .= '<p class="name" align="left" style="position: relative; height: 3em; overflow: hidden; color: black; font-weight: bold; text-decoration: none; font-size:13px; word-wrap: initial;" courseid="' . $courseid . '" moodleid="'.$USER->id.'" component="button">
					' . $courses. '</p>';
			$html .= '</button></a>';
			$data[$row][$count] = $html;
			$count++;
			if($count>2){
				$count = 0;
				$row++;
				$data[$row][0] = '';
				$data[$row][1] = '';
				$data[$row][2] = '';
			}
		}
		
		$coursetable->data = $data;
		echo html_writer::table($coursetable);
	}
	
	
	
	$record = new StdClass();
	$record->userid = $USER->id;
	$record->lastvisit = time();
	
	if($record = $DB->get_record("local_coursehub", array("userid" => $USER->id))) {
		$record->lastvisit = time();
		
		$DB->update_record("local_coursehub", $record);
	} else {
		$record = new StdClass();
		$record->userid = $USER->id;
		$record->timecreated = time();
		$record->lastvisit = $record->timecreated;
		
		$DB->insert_record("local_coursehub", $record);
	}
}if($action == 'assign'){
	$url = new moodle_url("/local/coursehub/index.php");
	$PAGE->set_context($context);
	$PAGE->set_url($url);
	$PAGE->set_pagelayout("standard");
	$PAGE->set_title(get_string("page_title", "local_coursehub"));
	$PAGE->set_heading(get_string("page_heading", "local_coursehub"));
	$PAGE->requires->jquery();
	$PAGE->requires->jquery_plugin ( 'ui' );
	$PAGE->requires->jquery_plugin ( 'ui-css' );
	
	echo $OUTPUT->header();
	
	$usercoursesql = "SELECT c.id, c.shortname
		FROM mdl_user u
		JOIN mdl_user_enrolments ue ON ue.userid = u.id
		JOIN mdl_enrol e ON e.id = ue.enrolid
		JOIN mdl_role_assignments ra ON ra.userid = u.id
		JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
		JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
		JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
		JOIN mdl_course_categories cc ON c.category = cc.id";
	$usercourse = $DB->get_records_sql($usercoursesql);
	
	$form = new coursehub_assign_form(null,array(
			"courses" => $usercourse
	));
	if (!$form->get_data()) {
		$form->display();
	} elseif ($data = $form->get_data()) {
		$arraycourses=array();
		foreach($usercourse as $course){
			$shortname = $course->shortname;
			if($data->$shortname){
				$arraycourses[] = $course->shortname;
				$assign = $DB->get_record('assign',array("id"=>1));
				$assign->id = null;
				$assign->course = $course->id;
				$assign->name = $data->name;
				$assign->intro = "<p>$data->name</p>";
				$assign->duedate = (strtotime('today') + 604800);
				$assign->allowsubmissionsfromdate = strtotime('today');
				$assign->timemodified = time();
				
				$id = $DB->insert_record("assign",$assign);
				
				$cm = $DB->get_record('course_modules',array("id"=>6));
				$cm->id = null;
				$cm->course = $course->id;
				$cm->instance = $id;
				$cm->section = 2;
				$cm->added = time();
				$id = $DB->insert_record("course_modules",$cm);
				echo $id;
				
				$sectionsql = "SELECT * FROM mdl_course_sections WHERE course=? AND section=?";
				$section = $DB->get_record_sql($sectionsql,array($course->id,2));
				if($section->sequence == ''){
					$section->sequence = $id;
				}else{
						$section->sequence = $section->sequence.",$id";
					}
				$id = $DB->update_record("course_sections",$section);
				
			}
			
		}
		shell_exec('php Applications/MAMP/htdocs/moodle/admin/cli/purge_caches.php');
		$return = new moodle_url("/local/coursehub/index.php");
		redirect($return);
		
	}
}
if($action == 'emarking'){
	$url = new moodle_url("/local/coursehub/index.php");
	$PAGE->set_context($context);
	$PAGE->set_url($url);
	$PAGE->set_pagelayout("standard");
	$PAGE->set_title(get_string("page_title", "local_coursehub"));
	$PAGE->set_heading(get_string("page_heading", "local_coursehub"));
	$PAGE->requires->jquery();
	$PAGE->requires->jquery_plugin ( 'ui' );
	$PAGE->requires->jquery_plugin ( 'ui-css' );
	
	echo $OUTPUT->header();
	
	$usercoursesql = "SELECT c.id, c.shortname
		FROM mdl_user u
		JOIN mdl_user_enrolments ue ON ue.userid = u.id
		JOIN mdl_enrol e ON e.id = ue.enrolid
		JOIN mdl_role_assignments ra ON ra.userid = u.id
		JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
		JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
		JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
		JOIN mdl_course_categories cc ON c.category = cc.id";
	$usercourse = $DB->get_records_sql($usercoursesql);
	$courseidarray = array ();
	foreach ( $usercourse as $courses ) {
		$course = explode("-",$courses->shortname);
		
		if(!in_array($course[2], $courseidarray)){
			// Only visible courses
			$courseidarray [] = $course[2];
			
		}
		
	}
	
	$form = new coursehub_emarking_form(null,array(
			"courses" => $courseidarray,
			"ids" => $ids
	));
	if (!$form->get_data()) {
		$form->display();
	} elseif ($data = $form->get_data()) {
		$usercoursesql = "SELECT c.id, c.shortname
		FROM mdl_user u
		JOIN mdl_user_enrolments ue ON ue.userid = u.id
		JOIN mdl_enrol e ON e.id = ue.enrolid
		JOIN mdl_role_assignments ra ON ra.userid = u.id
		JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
		JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
		JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'editingteacher'
		JOIN mdl_course_categories cc ON c.category = cc.id";
		$usercourse = $DB->get_records_sql($usercoursesql);
		$courseidarray = array ();
		foreach ( $usercourse as $courses ) {
			$course = explode("-",$courses->shortname);
			
			if(!in_array($course[2], $courseidarray)){
				// Only visible courses
				$courseidarray [] = $courses->id;
				
			}
			
		}
		$return = new moodle_url("/course/modedit.php",array("add"=>"emarking","section"=>"2","course"=>$courseidarray[$data->courses],"type"=>"1", "return" => "0", "sr"=>"0"));
		redirect($return);
	}
}
echo $OUTPUT->footer();