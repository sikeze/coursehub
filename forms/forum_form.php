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
* @copyright  2017 Mark Michaelsen (mmichaelsen678@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined("MOODLE_INTERNAL") || die();
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->libdir . "/formslib.php");
//require_once($CFG->dirroot."/local/sync/locallib.php");

// Form definition for synchronization creation
class forum_form extends moodleform {
	public function definition() {
		global $DB, $CFG, $USER;
		
		$mform = $this->_form;
		
		$mform->addElement("text", "title", "Título");
		$mform->setType("title", PARAM_RAW);
		
		$mform->addElement("editor", "textinput", "Mensaje");
		$mform->setType("textinput", PARAM_RAW);
		
		$coursessql = "SELECT c.id AS id,
						c.fullname AS fullname,
						c.shortname AS shortname
						FROM {course} AS c
						JOIN {enrol} AS e ON (c.id = e.courseid)
						JOIN {user_enrolments} AS uen ON (e.id = uen.enrolid)
						JOIN {user} AS u ON (uen.userid = u.id AND u.id = ?)";
		
		$params = array($USER->id);
		
		$selectcourses = array();
		if ($courses = $DB->get_records_sql($coursessql, $params)) {
			foreach ($courses as $course) {
				$selectcourses[$course->id] = $course->fullname." | ".$course->shortname;
			}
			
			$select = $mform->addElement("select", "courses", "Cursos", $selectcourses);
			$select->setMultiple(true);
		}
		
		
		$this->add_action_buttons($cancel = true, $submitlabel = "Enviar");		
	}
	
	public function validation($data, $files) {
		$errors = array();
		
		return $errors;
	}
}