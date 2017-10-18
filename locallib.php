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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.


/**
 * @package    local
 * @subpackage coursehub
 * @copyright  2017	Mihail Pozarski (mpozarski944@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Visible Course Module
define('COURSE_MODULE_VISIBLE', 1);
define('COURSE_MODULE_NOT_VISIBLE', 0);
// Visible Module
define('MODULE_VISIBLE', 1);
define('MODULE_NOT_VISIBLE', 0);

function get_total_notification($moodleid){
	global  $DB, $CFG;

	// Post parameters for query
	$totalpostparams = array(
			$moodleid
	);

	// Sql that counts all the posts since the last time the app was conected.
	$totalpostsql = "SELECT 	idcoursefd,
					count(countallpost) AS  countallpost
					FROM(SELECT discussions.course AS idcoursefd,
						COUNT(fp.id) AS countallpost
						FROM mdl_enrol AS en
						INNER JOIN mdl_user_enrolments AS uen ON (en.id = uen.enrolid)
						INNER JOIN mdl_forum_discussions AS discussions ON (en.courseid = discussions.course)
						INNER JOIN mdl_forum_posts AS fp ON (fp.discussion = discussions.id)
						INNER JOIN mdl_forum AS forum ON (forum.id = discussions.forum)
						INNER JOIN mdl_user AS us ON (uen.userid = us.id)
						INNER JOIN mdl_coursehub AS c ON (c.userid = us.id)
						WHERE fp.modified > c.lastvisit
						AND us.id = ?
						GROUP BY fp.id, discussions.course) AS tablewithdata
					GROUP BY idcoursefd";

	$totalpost = $DB->get_records_sql($totalpostsql, $totalpostparams);

	$totalpostpercourse = array();

	// Makes an array that associates the course id with the counted items
	if($totalpost){
		foreach($totalpost as $objects){
			$totalpostpercourse[$objects->idcoursefd] = $objects->countallpost;
		}
	}

	return array($totalpostpercourse);
}