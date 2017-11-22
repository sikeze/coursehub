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
/*
 * @package    local
 * @subpackage coursehub
 * @copyright  2017 Javier Gonzalez <javiergonzalez@alumnos.uai.cl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined("MOODLE_INTERNAL") || die();
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once(dirname(dirname(__FILE__)) . "/locallib.php");
require_once($CFG->libdir . "/formslib.php");


class coursehub_assign_form extends moodleform {
	public function definition() {
		
		$mform = $this->_form;
		$instance = $this->_customdata;
		$categoryid = $instance["courses"];
		$count=0;
		
		$mform->addElement('header', 'general', 'Tareas');
		$mform->addElement("text", "name", "Nombre de tareas");
		$mform->setType( "name", PARAM_TEXT);
		foreach($categoryid as $courses){
			$mform->addElement('checkbox', $courses->shortname, $courses->shortname);
			$count++;
		}
		$mform->addElement("hidden", "courses", $count);
		$mform->setType("action", PARAM_INT);
		$mform->addElement("hidden", "action", "assign");
		$mform->setType("action", PARAM_TEXT);
		
		$this->add_action_buttons(true, "Crear Tarea");
	}
	
	public function validation($data, $files){
		
	}
}
class coursehub_emarking_form extends moodleform {
	public function definition() {
		
		$mform = $this->_form;
		$instance = $this->_customdata;
		$courses = $instance["courses"];
		$ids = $instance["ids"];
		$mform->addElement('header', 'general', 'Emarking');
		$mform->addElement('select', 'courses', 'cursos', $courses);
		$mform->addElement("hidden", "action", "emarking");
		$mform->setType("action", PARAM_TEXT);
		$this->add_action_buttons(true, "Crear emarking");
	}
	
	public function validation($data, $files){
		
	}
}
