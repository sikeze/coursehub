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
require_once ("forms/forum_form.php");
global $CFG, $DB, $OUTPUT, $PAGE, $USER;

// User must be logged in.
require_login();
if (isguestuser()) {
	die();
}

$context = context_system::instance();

$url = new moodle_url("/local/coursehub/message.php");
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout("standard");
$PAGE->set_title(get_string("page_title", "local_coursehub"));
$PAGE->set_heading(get_string("page_heading", "local_coursehub"));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin ( 'ui' );
$PAGE->requires->jquery_plugin ( 'ui-css' );

$forumform = new forum_form();

echo $OUTPUT->header();

if($forumform->is_cancelled()) {
	$indexurl = new moodle_url("/local/coursehub/index.php");
	redirect($indexurl);
}

else if($data = $forumform->get_data()) {
	$discussionrecords = array();
	$postrecords = array();
	
	foreach($data->courses as $course) {
		$forumid = $DB->get_record("forum", array("course" => $course));
		$record = new stdClass();
		
		$record->course = $course;
		$record->forum = $forumid->id;
		$record->name = $data->title;
		$record->userid = $USER->id;
		$record->assessed = 0;
		$record->timemodified = time();
		$record->usermodified = $USER->id;
		
		if ($DB->insert_record("forum_discussions", $record)) {
			$discussionid = $DB->get_records_sql("SELECT d.id
												FROM {forum_discussions} AS d
												WHERE d.course = ?
												AND d.forum = ?
												AND d.userid = ?",
					array($course, $forumid->id, $USER->id)
					);
			
			$discussions = array();
			foreach ($discussionid as $id) {
				$discussions[] = $id->id;
			}
			
			$discussionid = max($discussions);
			$post = new stdClass();
			
			$post->discussion = $discussionid;
			$post->userid = $USER->id;
			$post->created = time();
			$post->modified = $post->created;
			$post->message = $data->textinput["text"];
			$post->messageformat = $data->textinput["format"];
			
			if ($DB->insert_record("forum_posts", $post)) {
				$postid = $DB->get_record("forum_posts", array("discussion" => $discussionid));
				$postid = $postid->id;
				
				$discussion = new stdClass();
				$discussion->id = $discussionid;
				$discussion->firstpost = $postid;
				
				if ($DB->update_record("forum_discussions", $discussion)) {
					echo "Mensaje enviado a curso $course<br>";
				}
			}
		}
	}
	
	$redirecturl = new moodle_url("/local/coursehub/index.php", array(
			"insert" => "success"
	));
	redirect($redirecturl);
}else {
	$forumform->display();
}

echo $OUTPUT->footer();