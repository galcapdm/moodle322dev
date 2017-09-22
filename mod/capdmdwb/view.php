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
 * Prints a particular instance of capdmdwb
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage capdmdwb
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// (Replace capdmdwb with the name of your module and remove this line)

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/filelib.php');
require_once('dwbtypebase.php');

    $PAGE->requires->yui_module('moodle-mod_capdmdwb-dwb', 'M.mod_capdmdwb.init_dwb', null);
    $PAGE->requires->jquery();  // We use the DataTables library which needs JQuery
    $PAGE->requires->js_call_amd('mod_capdmdwb/dwb', 'init', array());

    // Some CSS
    $PAGE->requires->css(new moodle_url("https://designers.hubspot.com/hs-fs/hub/327485/file-2054199286-css/font-awesome.css"));
    $PAGE->requires->css(new moodle_url("https://cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css"));

    $cmid  = optional_param('id',  0, PARAM_INT);   // course_module ID
    $sid   = optional_param('sid', -1, PARAM_INT);  // User id
    $tid   = optional_param('tid', -1, PARAM_INT);  // Topic id
    $tabid = optional_param('tabid', 0, PARAM_INT); // Current Tab id

    if ($cmid) {
        if (!$cm = get_coursemodule_from_id('capdmdwb', $cmid, 0, false, MUST_EXIST)) {
            error("Course Module ID was incorrect");
        }
	if (!$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST)) {
            error("Course is misconfigured");
        }
	if (!$capdmdwb = $DB->get_record('capdmdwb', array('id' => $cm->instance), '*', MUST_EXIST)) {
            error("Course module is incorrect");
	}
    } else {
        error('You must specify a course_module ID or an instance ID');
    }

    require_login($course, true, $cm);
    $context = context_module::instance($cm->id);

    // ============ Log this request.
    $params = array(
	'objectid' => $capdmdwb->id,
	'context'  => $context
    );
    $event = \mod_capdmdwb\event\course_module_viewed::create($params);
    $event->add_record_snapshot('capdmdwb', $capdmdwb);
    $event->trigger();


    /// Print the page header

    $PAGE->set_url('/mod/capdmdwb/view.php', array('id' => $cm->id));
    $PAGE->set_title(format_string($capdmdwb->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->set_context($context);

    // Output starts here
    echo $OUTPUT->header();

    if ($capdmdwb->intro) { // Conditions to show the intro can change to look for own settings or whatever
//        echo $OUTPUT->box(format_module_intro('capdmdwb', $capdmdwb, $cm->id), 'generalbox mod_introbox', 'capdmdwbintro');
    }

    // Create a DWB to render
    $dwbo = "dwb_".$capdmdwb->role_id;  // Role specifies the type

    // Load the class
    include_once("type/".$capdmdwb->role_id."/dwbtype.php");

    // Instantiaite it
    if (class_exists($dwbo)) {
       $mydwb = new $dwbo($capdmdwb, $course, $cm, $sid, $tid, $tabid);  // Create a specific instance

       echo($mydwb->render());  // Now output it
    }
    else {
       echo("<p>Cannot find DWB type: ".$dwbo."</p>");    	 
    }

    // Now that we've viewed the workbook, is it complete?
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && $capdmdwb->completionenabled) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
    }

    // ... and update the GradeBook
    capdmdwb_update_grades($capdmdwb, $USER->id);

    // Finish the page
    echo $OUTPUT->footer();

