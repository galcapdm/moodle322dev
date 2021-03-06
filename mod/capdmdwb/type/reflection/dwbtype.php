<?php

/**
 * DWB type class for the reflection type.
 *
 * @package    dwbtype
 * @subpackage reflectionessay
 * @copyright  CAPDM
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
    

defined('MOODLE_INTERNAL') || die();


/**
 * The reflection dwb type.
 *
 * @copyright  CAPDM
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dwb_reflection extends dwb_type 
// ==================================
{
    private $dwbrole = "reflection";
    
    private function utf_hexup($str) {
        return preg_replace("/%u(....)/", "&#x$1;", $str);
    }
    
    /**
     * @return nothing
     */
    public function render() 
    // ---------------------
    {
	global $CFG, $USER, $DB, $PAGE, $COURSE;
	
	$output = '';  // Build up the page
	
	$cm  = $this->cm;   $course = $this->course;    $capdmdwb = $this->capdmdwb;
	$sid = ($this->sid == -1) ? 0 : $this->sid;  // This may be -1

        // =========== The Digital Workbook ===================
	
        $act_id = optional_param('act_id', 'no_element', PARAM_TEXT);  // Pick up whatever element we might be tracking

        $showlinks = true;   $viewother = false;
        if ($sid == 0) { $sid = $USER->id; } 
	else {
	    $student = $DB->get_record('user', array('id' => $sid), '*', MUST_EXIST);   $showlinks = false; 
	}
        // This is just to ensure that we use the DWB variable as the user name.  Might
        // be set by the tutor looking at someone else's DWB
	
        $nm = $USER->firstname." ".$USER->lastname;
        $context = context_module::instance($this->cm->id);
	
	if ($sid == $USER->id) {
            $output .= html_writer::tag('h1', html_writer::tag('i', '', array('class' => 'fa fa-pencil-square-o', 'style' => 'font-weight: bold;')).
					'&nbsp;'.get_string('myworkbook', 'capdmdwb').$nm, 
					array('class' => 'dwb-header'));
	}
	else $output .= html_writer::tag('h1', 'Reviewing', array('class' => 'dwb-header'));

        $arrFields = array();    $arrEntries = array();

	$output .= html_writer::start_tag('div', array('class' => "dwb-tutor"));  // Div 0 +

        // are we viewing our own DWB or are we someone with permissions to view someone else's?
        if ($sid == $USER->id && has_capability("mod/capdmdwb:share", $context)) {
	    // get a list of students enorlled on this course
	    $studentlist = $DB->get_records_sql('SELECT distinct u.id, u.firstname, u.lastname, u.firstaccess, u.lastaccess,u.city, u.country 
	                                         FROM {capdmdwb_response} r INNER JOIN {user} u ON r.user_id = u.id 
						 WHERE course = :course ORDER BY u.lastname, u.firstname', array('course' => $course->id));
	    
	    foreach($studentlist as $s) {
		$arrFields['id'] = $s->id;
		$arrFields['fullname']    = $s->firstname." ".$s->lastname;
		$arrFields['firstaccess'] = date('dS F Y',$s->firstaccess); $arrFields['lastaccess'] = date('dS F Y',$s->lastaccess);
		$arrFields['city']        = $s->city.", ".$s->country;
		$arrFields['dwb_link']    = html_writer::tag('a', get_string('viewdwb','capdmdwb'), 
							     array('href' => 'view.php?id='.$_GET['id'].'&sid='.$s->id, 'target' => '_dwb'));
//		$arrFields['dwb_link'] = '<a href="view.php?id='.$_GET['id'].'&sid='.$s->id.'" target="_dwb">'.get_string('viewdwb','capdmdwb').'</a>';
		array_push($arrEntries, $arrFields);
	    }
	    
	    // Build a table for arranging output
	    $table = new html_table();
	    $table->id = "capdmdwb_student_dwbs";

	    $table->head = array('User ID', 'Name','First access','Last access','City, Country','');
	    $table->data = $arrEntries;
	    
	    $output .= html_writer::start_tag('div', array('class' => "infomessage highlight", 'style' => "margin-bottom: 1em;")); // Div 1 +
	    $output .= html_writer::tag('a', get_string('viewstudentlist','capdmdwb'), array('id' => 'toggle_studentlist', 'name' => 'toggle_studentlist'));
	    $output .= html_writer::end_tag('div');  // Div 1 -
	    
	    $output .= html_writer::start_tag('div', array('id' => 'studentlist', 'class' => 'display-toggle-detail')); // Div 1 +
	    
	    if (sizeof($studentlist) > 0) {
	        $output .= html_writer::table($table);
	    }
	    else $output .= html_writer::tag('p', get_string('nostudentworkbooks','capdmdwb'), array('class' => 'highlight'));
	    
	    $output .= html_writer::end_tag('div');  //Div 1 -
	}
	
	if ($sid != $USER->id && has_capability("mod/capdmdwb:share", $context)) {
   	    $viewother = true;
	    $nm = $student->firstname." ".$student->lastname;
	    $output .= html_writer::tag('p', get_string('viewinganotherdwb','capdmdwb').
					html_writer::tag('strong', $nm.', '.(($student->city != '') ? $student->city.', ' : '').$student->country), 
					array('class' => 'highlight'));
	} else {
	    $viewother = false;
	}

	$output .= html_writer::tag('p', get_string('reflections', 'capdmdwb'));	    
	$output .= html_writer::tag('p', get_string('pdfpresummary', 'capdmdwb').
				    html_writer::tag('a', get_string('pdfbooklet', 'capdmdwb'), 
						     array('class' => 'pdfprnicon', 'href' => $CFG->wwwroot.'/mod/capdmdwb/workbook.php?id='.$cm->id.'&dwb='.$sid.'&nm='.urlencode($nm).'&checksum='.md5($sid.'this is unguessible'), 'target' => '_blank')).get_string('pdfpostsummary', 'capdmdwb'));
	$output .= html_writer::end_tag('div');  // Div 0 -
	$output .= html_writer::tag('div', '', array('class' => 'clearfix'));  // Does this sort the newline issue?  Div +/-

	// Are we part of a DWB Grouping?  If so, then add links to your partners
	// ----------------------------------------------------------------------
	$ggid = groups_get_grouping_by_name($course->id, _CAPDMDWB);
	if ($showlinks && $ggid != false) {
      	    if (($g = groups_get_all_groups($course->id, $USER->id, $ggid)) != false) {
		// We are in a group within the DWB grouping, so find out who else is.
		
		$output .= html_writer::start_tag('div', array('id' => 'dwb-groups', 'class' => 'dwb-groups yui3-accordion'));  // Div 0 +
		
		$output .= html_writer::tag('a', get_string('groups', 'capdmdwb').get_string('share', 'capdmdwb'), 
					    array('href' => 'javascript:void(null);', 'title' => get_string('groups', 'capdmdwb'))); 
		    
		$output .= html_writer::start_tag('div', array('id' => 'dwb-group-item', 'class' => 'yui3-accordion-item'));  // Div 1 +

		foreach ($g as $gi) {
		    if (groups_is_member($gi->id, $USER->id)) {  // We are in it, who else?
			if (($gms = groups_get_members($gi->id)) != false) {  // We are not alone
			    $output .= html_writer::start_tag('div');  // Div 2 +
			    $output .= html_writer::tag('p', get_string('grouping', 'capdmdwb'),
							html_writer::start_tag('a', array('href' => '../capdmcourse/xllredirect.php?course='.$course->id.'&xll=dwb-forum-'.$course->idnumber.'.xll', 'alt' => get_string('forum', 'capdmdwb'))));
			    $output .= html_writer::tag('span', get_string('here', 'capdmdwb'));
			    $output .= html_writer::empty_tag('img', array('src' => '../../theme/'.current_theme().'/pix/mod/forum/icon.gif'));
			    $output .= html_writer::empty_tag('br');
			    
			    $i = 1;
			    foreach ($gms as $gm) {
				if ($gm->id != $USER->id)
				    $output .= html_writer::tag('span', $i.'. '.$gm->firstname.' '.$gm->lastname.
								html_writer::tag('a', array('href' => 'view.php?id='.$id.'&nm='.$gm->firstname.' '.$gm->lastname.'&selecteddwb='.$gm->id.'&mini=1', 'alt' => get_string('view', 'capdmdwb'), 'target' => '_dwb')).
								html_writer::tag('img', array('src' => '../../theme/'.current_theme().'/pix/i/group.gif')));		     
				$output .= html_writer::end_tag('a');
				$output .= html_writer::empty_tag('br');
			    }
			    $output .= html_writer::end_tag('div');   // Div 2 -
			}
		    }
		}
		
		$output .= html_writer::end_tag('div');  // Div 1 -
		$output .= html_writer::end_tag('div');  // Close off the hide divs Div 0 -
     	    }
        }
	    
        // NEW LAYOUT START
        // check to see if we are going to display the current user's workbook or are we attempting to see antother user's workbook (must be an admin or similar to do this)
        if (has_capability("mod/capdmdwb:share", $context)) $userid = $sid;
        else $userid = $USER->id;

        $strSQL = "SELECT act.id, wrap.topic_no, wrap.session_no, act.course, wrap.mod_id, wrap.wrapper_id, act.activity_id, act.data_type, 
	                  act.qpart AS qpart, wrap.title, wrap.preamble, wrap.run_order, resp.data_id AS data_id, 
			  resp.data_value AS data_value, resp.data_option AS data_option, resp.data_explanation AS data_explanation, 
			  resp.response_include AS response_include 
			  FROM {capdmdwb_activity} act 
			  LEFT JOIN (SELECT data_id, data_value, data_option, data_explanation, data_type, response_include 
                                     FROM {capdmdwb_response} 
				     WHERE course = :course1 AND user_id = :userid) resp 
                          ON act.activity_id = resp.data_id 
			  LEFT JOIN {capdmdwb_wrapper} wrap 
			  ON act.wrapper_id = wrap.wrapper_id AND wrap.course=act.course 
			  WHERE wrap.course = :course2 AND wrap.role_id=:dwbrole 
			  ORDER BY wrap.topic_no, wrap.session_no, wrap.run_order";

        $rs = $DB->get_recordset_sql($strSQL, 
				     array('course1' => $course->id, 'userid' => $userid ,'course2' => $course->id, 'dwbrole' => $this->dwbrole));
	
	if (!$rs->valid()) {
	    $output .= html_writer::tag('h2', get_string('noworkbook','capdmdwb'), array('id'=>'noworkbook'));
	} else {
	    // Collate the actual output in this variable then put ut at end
     	    $dwb_summary = html_writer::start_tag('div', array('id' => 'summary_detail', 'class' => 'dwb-detail'));  // Div S0 +
      	    $dwb_summary .= html_writer::start_tag('ul', array('class' => 'dwb-boxes'));
	    
	    $lastwrapper = ""; $lastactivity = ""; $sameactivity = 0;
	    $odd_even = " dwb-group-odd"; $item_highlight = "";
	    $strTopic = get_string('topic','capdmdwb');	    $strTopic0 = get_string('topic0','capdmdwb');
	    
	    $responses = array(); $responsecounts = array();  // Keep a tally of how many activities there are in a task

	    // Go through the rows building up the DWB Summary
	    $lastactivitygroup = 1;    $odd_even = ' dwb-group-odd';  $done = 0;  $notdone = 0;  

	    // Keep a track of the index of actual tabs, not topics, as this selects the tab itself
	    // $tabid uses the topic number to provide a label for the tab, but not its index.
	    $tabindex = 0;  

	    foreach ($rs as $row){
                $item_highlight = (strpos($row->activity_id, $act_id) > -1) ? " dwb-highlight" : "";  // Is this the active item?  KWC
		
		if ($lastactivity == $row->wrapper_id)  // Seen this so don't count it again (MRQ?), but count activities in task
		    $sameactivity += 1;
		else {
		    $lastactivity = $row->wrapper_id;  $sameactivity = 0;  // Reset counter
		}

		// need to do this on the first pass through so there is a value for lastactivitygroup
		if ($lastactivitygroup != $row->session_no){
		    $odd_even = ($odd_even == ' dwb-group-odd') ? ' dwb-group-even' : ' dwb-group-odd';
		}
		
		$tabid = $row->topic_no;  // This has to reflect the actual number of tabs, not strictly the topic no.
		if ($lastwrapper == $row->topic_no) {  // We are in the same topic as before
		    if (strlen($row->data_value) != 0) {  // Has been filled in
			$dwb_summary .= html_writer::tag('li', html_writer::tag('a', html_writer::tag('i', '', array('class' => 'fa fa-pencil-square-o', 'style' => 'font-weight: bold;')), array('class' => 'dwb-goto-tab', 'act_id' => $row->activity_id, 'tabid' => $tabindex, 'title' => 'Click here to go to this activity')), array('class' => 'dwb-done'.$item_highlight.$odd_even));
			$done++;
		    } else {
			$dwb_summary .= html_writer::tag('li', html_writer::tag('a', html_writer::tag('i', '', array('class' => 'fa fa-square-o', 'style' => 'font-weight: bold;')), array('class' => 'dwb-goto-tab', 'act_id' => $row->activity_id, 'tabid' => $tabindex, 'title' => 'Click here to go to this activity')), array('class' => 'dwb-notdone'.$item_highlight.$odd_even));
			$notdone++;
		    }
		} else {
		    $dwb_summary .= html_writer::end_tag('ul');  // Into a new topic so close the UL off
		    $dwb_summary .= ($tabid==0) ? $strTopic0 : $strTopic.' '.$row->topic_no.': '.$row->title;  // The word Topic
		    $dwb_summary .= html_writer::start_tag('ul', array('class' => 'dwb-boxes'));

		    $tabindex++;  // New tab index only with a new topic
		    if (strlen($row->data_value) != 0) {
			$dwb_summary .= html_writer::tag('li', html_writer::tag('a', html_writer::tag('i', '', array('class' => 'fa fa-pencil-square-o', 'style' => 'font-weight: bold;')), array('class' => 'dwb-goto-tab', 'act_id' => $row->activity_id, 'tabid' => $tabindex, 'title' => 'Click here to go to this activity')), array('class' => 'dwb-done'.$item_highlight.$odd_even));
			$done++;
		    } else {
			$dwb_summary .= html_writer::tag('li', html_writer::tag('a', html_writer::tag('i', '', array('class' => 'fa fa-square-o', 'style' => 'font-weight: bold;')), array('class' => 'dwb-goto-tab', 'act_id' => $row->activity_id, 'tabid' => $tabindex, 'title' => 'Click here to go to this activity')), array('class' => 'dwb-notdone'.$item_highlight.$odd_even));
			$notdone++;
		    }
		}
		
		$lastwrapper = $row->topic_no;    $lastactivitygroup = $row->session_no;  // Update some pointers

		if ($row->data_type != "select") {  // use wrapper ID for select as Activity ID is different 
		    array_push($responses, array($row->topic_no, $row->session_no, $row->data_id, $row->data_value, $row->data_option, $row->data_explanation, $row->qpart, $row->data_type, $row->wrapper_id, $row->activity_id, $row->title, $row->preamble));
		    $responsecounts[$row->activity_id] = $sameactivity;  // Keep a count
		}
		else {
		    array_push($responses, array($row->topic_no, $row->session_no, $row->data_id, $row->data_value, $row->data_option, $row->data_explanation, $row->qpart, $row->data_type, $row->wrapper_id, $row->wrapper_id, $row->title, $row->preamble));
		    $responsecounts[$row->wrapper_id] = $sameactivity;  // Keep a count
		}
	    }

	    // Close off the summary section
	    $dwb_summary .= html_writer::end_tag('ul');
	    $dwb_summary .= html_writer::end_tag('div');  // Div 0 -

	    // Some constants. Build up an array of arrays of responses
	    define ('_TOPICNO', 0);	    define ('_SESSNO', 1);	    define ('_DATAID', 2);    // 0 = Topic No; 1 = Session no; 2 = Data ID (Response ID)
	    define ('_DATAVAL', 3);	    define ('_DATAOPT', 4);	    define ('_DATAEXP', 5);   // 3 = Data Value; 4 = Data Option; 5 = Data Explanation
	    define ('_QPART', 6);	    define ('_DATATYPE', 7);	    define ('_WRAPID', 8);     // 6 = QPart; 7 = Data Type; 8 = Wrapper ID
	    define ('_ACTID', 9);           define ('_TITLE', 10);	    define ('_PREAMB', 11);    // 9 = Activity ID; 10 = Title; 11 = Preamble

	    // Set up to assume there is no Topic 0, which tends to be a preamble.  Adjust accordingly
	    $dwb_topics = array();
	    $topic0 = 1;  $lastTopic = -99;  $lastSession = 0;  $sameactivity = 0;  $i = 1; $lastactivity = "";  // Used to suppress repeated qparts, e.g. for MRQs
	    
	    foreach ($responses as $response) {  // Go through all the responses, looking at the detail
		$thisResponse = '';

		// First check to see if this is a new topic.  This will happen with the first record
		if ($lastTopic != $response[_TOPICNO]) {  // Topic No in position 0
		    if ($lastTopic != -99) $dwb_topics[$i] .= html_writer::end_tag('div');  // Div 0

		    $i = $response[_TOPICNO];      // Query is ordered by Topic No so always increases, but not necessarily by 1
		    if ($i == 0) $topic0 = 0;      // used later to put out tabs, including a possible Topic 0 (e.g. Preface)

		    $dwb_topics[$i] = html_writer::start_tag('div', array('class' => 'dwb-topic'));  // Add a new element with the key = $i Div 0
		}

		// Now is this an activity that we've seen before.  Selects have the same wrapper ID but different Activity IDs
		if ($lastactivity == $response[_WRAPID])  // Seen this so don't count it again (MRQ?), but count the part occurrences
		    $sameactivity += 1;
		else {
		    $lastactivity = $response[_WRAPID];  $sameactivity = 0;
		}

		// if there is no data_id (i.e. a response) then display a message accordingly
		if (strlen($response[_DATAID]) == 0) {
		    $thisResponse = html_writer::tag('span', get_string('noresponse','capdmdwb'), array('class' => 'highlighttext'));
		} 
		else {
		    // now check what type of response it is
		    switch ($response[_DATATYPE]) {
		    case "textarea":
//			$thisResponse = '<form class="dwb-activity" name="demo1_dwb0101_form"><input type="hidden" name="dwb-instance" value="demo1_dwb0101_fib"><input type="hidden" name="dwb-format" value="textarea"><textarea class="dwb-textarea" id="demo1_dwb0101_fib" rows="3" cols="80">'.nl2br(htmlspecialchars($response[_DATAVAL])).'</textarea></form>';
			$thisResponse = nl2br(htmlspecialchars($response[_DATAVAL]));
			break;
		    case "textbox":
//<input type="hidden" name="dwb-instance" value="demo1_dwb0201_fib"><input type="hidden" name="dwb-format" value="textbox"><input value="'.nl2br(htmlspecialchars($response[_DATAVAL])).'" type="text" class="dwb-textbox" id="demo1_dwb0201_fib" size="25"></form>';
	 	        $thisReponse = nl2br(htmlspecialchars($response[_DATAVAL]));
			break;
		    case "mcq":
			$thisResponse = html_writer::tag('span', $this->utf_hexup($response[_DATAEXP]), array('class' => 'dwb-radio-icon'));
			break;
		    case "select":		
			$thisResponse = html_writer::tag('span', $this->utf_hexup($response[_DATAOPT]), array('class' => 'dwb-select-icon'));
			break;
		    case "mansopt":
			$thisResponse = html_writer::tag('span', htmlspecialchars($this->utf_hexup($response[_DATAEXP])), array('class' => 'dwb-radio-icon'));
			break;
		    case "mrq":
			$thisResponse = html_writer::tag('span', $this->utf_hexup($response[_DATAEXP]), array('class' => 'dwb-checkbox-icon'));
			break;
		    case "highlight":
			$thisResponse = html_writer::tag('span', $this->utf_hexup($response[_DATAVAL]), array('class' => 'dwb-highlight'));
		        break;
		    }
		}

		// New Session
		if ($response[_SESSNO] != $lastSession) {  // Put up a new Title
		    $dwb_topics[$i] .= html_writer::tag('div', $response[_TITLE], array('class' => 'dwb-detail-group-title'));  // Div +/-
		}
		
		$dwb_topics[$i] .= html_writer::tag('a', '', array('name' => $response[_ACTID]));  // Prepare to open Div 2

		if ($sameactivity == 0) {  // Opens a DIV for a new activity
		    if (strlen($response[_PREAMB]) > 0) {  // Is there a Preamble
			$dwb_topics[$i] .= html_writer::tag('div', $response[_PREAMB], array('class' => 'dwb-detail-group-preamble'));  // Div +/-
		    }

		    $dwb_topics[$i] .= html_writer::start_tag('div', array('id' => 'dwb-detail_'.$response[_DATAID], 'class' => 'dwb-response-qpart'));  // Div 1 +
		    $dwb_topics[$i] .= html_writer::tag('div', $response[_QPART], array('id' => 'dwb-detail-expander_'.$response[_DATAID], 'class' => 'dwb-qpart-expander', 'style' => 'white-space: pre-wrap'));  // QPart Div +/-
		}
		else {
		    if ($response[_DATATYPE] == 'select') {  // There is a seperate QPart
			$dwb_topics[$i] .= html_writer::start_tag('div', array('id' => 'dwb-detail_'.$response[_DATAID]));  // Div 1 + (alt)
		    }
		    else if ($response[_DATATYPE] == 'mrq') {  // Need to keep <div> count in step
			$dwb_topics[$i] .= html_writer::start_tag('div', array());
		    }
		    else {
			$dwb_topics[$i] .= html_writer::start_tag('div', array('id' => 'dwb-detail_'.$response[_DATAID], 'class' => 'dwb-response-qpart'));  // Div 1 + (alt)
		    }
		    
		    // Don't repeat for mrqs in same activity
		    if ($response[_DATATYPE] != 'mrq') $dwb_topics[$i] .= html_writer::tag('div', $response[_QPART], 
							array('id' => 'dwb-detail-expander_'.$response[_DATAID], 
							      'class' => 'dwb-qpart-expander', 'style' => 'white-space: pre-wrap'));  // QPart Div +/-
		}

		$dwb_topics[$i] .= html_writer::end_tag('div');  // Div 1 -
		$dwb_topics[$i] .= html_writer::tag('div', $thisResponse, array('class' => 'dwb-response-detail')); // Div +/-

		if ($sameactivity == $responsecounts[$response[_ACTID]]) {  // Are we at the last of a set?
		    $dwb_topics[$i] .= html_writer::start_tag('div', array('class' => 'dwb-response-editlink'));  // Div 2 + (poss)
		    $dwb_topics[$i] .= html_writer::tag('a', 'Back to summary', array('href' => '#dwb-details', 'class' => 'dwb-goto-summary'));
		    
		    // if we are viewing a student record then do not show link back to course
		    if (!$viewother) {
			$dwb_topics[$i] .= ' - '.html_writer::tag('a', get_string('editthisactivityentry','capdmdwb'), 
					     array('href' => '../capdmcourse/xllredirect.php?course='.$course->id.'&xll='.$response[_ACTID]));  
		    }

		    $dwb_topics[$i] .= html_writer::end_tag('div');  // Close div 2 -
		}
		
		$lastTopic = $response[_TOPICNO];	$lastSession = $response[_SESSNO];
	    }  // Foreach

	    // Close off the the last foreach block DIVs
//	    $dwb_topics[$i] .= html_writer::end_tag('div');  // Closes all divs

	    // Now the Tabs
	    $str_summary = get_string('summarytab','capdmdwb');  // $instruction = get_string('instruction','capdmdwb');    
	    $output .= html_writer::start_tag('div', array('id' => 'dwb-details'));  // Div 0 +
//	    $output .= html_writer::tag('p', $instruction);
	    $output .= html_writer::start_tag('ul');
	    $output .= html_writer::tag('li', html_writer::tag('a', $str_summary ,array('href' => '#summary')));

	    // Tabs may start at 0.  Only put put tabs with content
	    for ($i=$topic0; $i<=$tabid; $i++) {  // Output the tab informaton with the topic title
		if (array_key_exists($i, $dwb_topics) == true) 
		    $output .= html_writer::tag('li', html_writer::tag('a', (($i==0) ? $strTopic0 : ($strTopic.' '.$i)), array('href' => '#tab-'.$i)));
	    }
	    $output .= html_writer::end_tag('ul');

	    // The Details.  Again only these with content.
	    $output .= html_writer::start_tag('div', array('class' => 'dwb-reflection'));  // Div 1 +

	    $output .= html_writer::start_tag('div', array('id' => 'summary'));  // Div 2 +
	    $output .= html_writer::tag('p', $dwb_summary);
	    $output .= html_writer::end_tag('div');  // Div 2 -

	    for ($i=$topic0; $i<=$tabid; $i++) {
		if (array_key_exists($i, $dwb_topics) == true) {
		    $output .= html_writer::start_tag('div', array('id' => '#tab-'.$i));  // Div 2 +
		    $output .= html_writer::tag('p', $dwb_topics[$i]);
		    $output .= html_writer::end_tag('div');  // Div 2 -
		}
	    }
	    
	    $output .= $this->capdmdwb_key();  // This prints the key at the bottom.

	    $output .= html_writer::end_tag('div');  // Div 1 --  needed
	    $output .= html_writer::end_tag('div');  // Div 0 --

	    $output .= html_writer::end_tag('div');  // Div EXTRA - seems to be needed to close up navigation
 	}
	
	$rs->close();

	return $output;
    }

    private function capdmdwb_key() {
    // ----------------------------

	$output = html_writer::start_tag('div', array('class' => 'dwb-key'));;
	$output .= html_writer::start_tag('ul', array('id' => 'dwb-key', 'class' => 'dwb-boxes'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-square-o', 'style' => 'font-weight: bold;')).get_string('box_notdone', 'capdmdwb'), 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-pencil-square-o', 'style' => 'font-weight: bold;')).get_string('box_done', 'capdmdwb'), 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-pencil-square-o dwb-highlight', 'style' => 'font-weight: bold;')).get_string('box_highlight', 'capdmdwb'), 
				array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::end_tag('ul');
	$output .= html_writer::end_tag('div');

	return $output;
    }
    
} // End of Class
