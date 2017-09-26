<?php

require_once($CFG->libdir.'/formslib.php');
require_once('locallib.php');

class local_capdmhelpdesk_form extends moodleform {
    function definition() {

                global $COURSE, $DB, $CFG;

                $crseFullname = null;

                $mform = $this->_form;

                $mform->addElement('html', '<div id="capdmcontactus_form">');

                // Helpdesk menu
                $mform->addElement('advcheckbox', 'helpdeskmenuitem', get_string('showoncustommenu', 'local_capdmhelpdesk'), get_string('showoncustommenudesc', 'local_capdmhelpdesk'), array(), array(0, 1));

                $mform->addelement('hidden', 'thisaction', 1);
		$mform->setType('thisaction', PARAM_INT);

                // add button
                $buttonarray=array();
                $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
                $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
                $mform->closeHeaderBefore('buttonar');

                $mform->addElement('html', '</div>');
    }

    function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        if($errors){
            foreach($errors as $e=>$v){
            	switch($e){
                	default:
                    	echo get_string('error', 'local_capdmhelpdesk');
                    	break;
                }
            }
        }

        return $errors;

    }

}