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
 * The main capdmdwb configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage capdmdwb
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_capdmdwb_mod_form extends moodleform_mod {
    
    /**
     * Defines forms elements
     */
    public function definition() {
        global $COURSE;
	
        $mform = $this->_form;
	
	//-------------------------------------------------------------------------------
	/// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
	
	/// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('capdmdwbname', 'capdmdwb'), array('size'=>'64'));
	$mform->setType('name', PARAM_TEXT);
	$mform->addRule('name', null, 'required', null, 'client');
	
        $this->standard_intro_elements(get_string('description', 'capdmdwb'));
	
	// ----------- role ----------------------------------------------------------------
        $options = array(
            'reflection' => 'Reflection',
            'planner'    => 'Planner',
            'rra'        => 'RRA',
            'form'       => 'Form',
            'journal'    => 'Journal');
        $mform->addElement('select', 'role_id', get_string("role_id", "capdmdwb"), $options);
        $mform->setDefault('role_id', 'reflection');  // i.e. reflection
	
	// ------------ TOT entries  ------------------------------
        $options = array(  // Likert Scale for rating understanding of Learning Objectives
            3 => '3',
            5 => '5');
        $mform->addElement('select', 'numtotopts', get_string("numtotopts", "capdmdwb"), $options);
        $mform->setDefault('numtotopts', 3);  // i.e. 3
	
	// ------------ To support Form Type DWBs -----------------------------------------------------
	//	Adding the required field for the report server name 
	$mform->addElement('text', 'repserver', get_string('dwbrepserver', 'capdmdwb'), array('size'=>'80'));
	$mform->setType('repserver', PARAM_TEXT);
	$mform->addRule('repserver', null, 'required', null, 'client');
	//        $mform->setHelpButton('repserver', array('repserver', get_string('helprepserver', 'capdmdwb'), 'capdmdwb'));
	
	//	Adding the required field for the report server username 
	$mform->addElement('text', 'rsuser', get_string('dwbrsuser', 'capdmdwb'), array('size'=>'20'));
	$mform->setType('rsuser', PARAM_TEXT);
	$mform->addRule('rsuser', null, 'required', null, 'client');
	//        $mform->setHelpButton('rsuser', array('rsuser', get_string('helprsuser', 'capdmdwb'), 'capdmdwb'));
	//	Adding the required field for the report server pass
	$mform->addElement('text', 'rspass', get_string('dwbrspass', 'capdmdwb'), array('size'=>'20'));
	$mform->setType('rspass', PARAM_TEXT);
	$mform->addRule('rspass', null, 'required', null, 'client');
	//        $mform->setHelpButton('rspass', array('rspass', get_string('helprspass', 'capdmdwb'), 'capdmdwb'));
	
	//	Adding the required field for the report server form
	$mform->addElement('text', 'rsform', get_string('dwbrsform', 'capdmdwb'), array('size'=>'50'));
	$mform->setType('rsform', PARAM_TEXT);
	$mform->addRule('rsform', null, 'required', null, 'client');
	//        $mform->setHelpButton('rsform', array('rsform', get_string('helprsform', 'capdmdwb'), 'capdmdwb'));
	
	//      Output types
        $optionsp = array(
            0 => 'pdf',
            1 => 'docx',
            2 => 'rft',
            3 => 'odt',
	    4 => 'xlsx');
        $mform->addElement('select', 'rsop', get_string("dwbrsop", "capdmdwb"), $optionsp);
        $mform->setDefault('rsop', 0);  // i.e. pdf
	
	
	// ------------------------------------------
	
	// This line must appear here.  Not onoly does it add the ID feature, but it also
	// seems to tie everything together.  Without it there seems to be a lack
	// of course information.
        $this->standard_coursemodule_elements(array('groups'=>false, 'groupmembersonly'=>true, 'gradecat'=>false));
	
        // add standard buttons, common to all modules
        $this->add_action_buttons();
	
    }
    
    function add_completion_rules() 
    // ----------------------------
    {
        $mform =& $this->_form;
	
        $group=array();
        $group[] =& $mform->createElement('checkbox', 'completiontasksenabled', '', get_string('dwbtaskcompletion', 'capdmdwb'));
        $group[] =& $mform->createElement('checkbox', 'completionenabled', '', get_string('dwbcompletion', 'capdmdwb'));
        $mform->addGroup($group, 'completiongroup', get_string('dwbcompletiongroup', 'capdmdwb'), array(' '), false);

        // The enabling checkbox is disabled if the completion checkbox is not ticked!  Not sure why???
        $mform->disabledIf('completionenabled', 'completiontasksenabled', 'notchecked');
	
        return array('completiongroup');

    }
    
    function completion_rule_enabled($data) 
    // ------------------------------------
    {
        return (!empty($data['completionenabled']));
    }
    
    function get_data() 
    // ----------------
    {
        $data = parent::get_data();
	
        if (!$data) {
            return false;
        }
        // Turn off completion settings if the checkbox ain't ticked
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completiontasksenabled) || !$autocompletion) {
                $data->completionenabled = 0;
            }
        }
	
        return $data;
    }
}
