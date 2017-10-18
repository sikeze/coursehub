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
global $CFG, $DB, $OUTPUT, $PAGE, $USER;

// User must be logged in.
require_login();
if (isguestuser()) {
	die();
}

$context = context_system::instance();


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

echo html_writer::tag('h3','Notificaciones');
echo html_writer::start_tag('div',array('style' => 'width:100%; height: 210px; overflow:auto;'));
echo '<ul class="collection with-header">
        <li class="collection-item"><div>Juan Fernando entrego tarea "tarea1" en curso "2220-S-MAT106-2-2-2017".<a href="#!" class="secondary-content"><i class="material-icons">send</i></a></div></li>
        <li class="collection-item"><div>Mark henriquez entrego tarea "tarea1" en curso "2220-S-MAT106-2-2-2017".<a href="#!" class="secondary-content"><i class="material-icons">send</i></a></div></li>
        <li class="collection-item"><div>Joaquin Rivano escribio en el foro del curso "2220-S-MAT118-2-2-2017".<a href="#!" class="secondary-content"><i class="material-icons">send</i></a></div></li>
        <li class="collection-item"><div>Pedro Picapiedra escribio en el foro del curso "2220-S-CORE102-2-2-2017".<a href="#!" class="secondary-content"><i class="material-icons">send</i></a></div></li>
		<li class="collection-item"><div>Juan Ortiz escribio en el foro del curso "2220-S-LITR108-2-2-2017".<a href="#!" class="secondary-content"><i class="material-icons">send</i></a></div></li>
      </ul>';
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
$url = new moodle_url('/');
$table->data = array(
		array($OUTPUT->single_button($url, 'Escribir en foros'),$OUTPUT->single_button($url, 'Agregar eMarkings'),$OUTPUT->single_button($url, 'Agregar Tareas'), $OUTPUT->single_button($url, 'Agregar Encuestas'))
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

$usercourse = enrol_get_users_courses ( $USER->id );
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
	$totals = $courses->totalnotifications;
	
	$html = '<button type="button" class="btn btn-info btn-lg" style="white-space: normal; width: 90%; height: 90%; border: 1px solid lightgray; background: #F0F0F0;" courseid="' . $courseid . '" fullname="' . $fullname . '" moodleid="'.$USER->id.'" component="button">';
	$html .= '<p class="name" align="left" style="position: relative; height: 3em; overflow: hidden; color: black; font-weight: bold; text-decoration: none; font-size:13px; word-wrap: initial;" courseid="' . $courseid . '" moodleid="'.$USER->id.'" component="button"> 
				' . $fullname . '</p>';
	$html .= '</button>';
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

echo $OUTPUT->footer();