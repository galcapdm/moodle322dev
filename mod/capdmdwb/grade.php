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
 * This page is the entry page into the capdmdwb UI. Displays information about the
 * capdmdwb to students and teachers, and lets students see their previous attempts.
 *
 * @package   mod_capdmdwb
 * @category  grade
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/capdmdwb/locallib.php');

$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('capdmdwb', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$capdmdwb = $DB->get_record('capdmdwb', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);


// Send the user to the DWB
redirect(new moodle_url('/mod/capdmdwb/view.php', array('id' => $cm->id)));
