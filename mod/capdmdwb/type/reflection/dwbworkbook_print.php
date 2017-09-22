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
class dwb_workbook_reflection extends dwb_workbook_type {
    private $dwbrole = "reflection";
    
    private function utf_hexup($str) {
        return preg_replace("/%u(....)/", "&#x$1;", $str);
    }
    
    /**
     * @return nothing
     */
    public function render() {
	
	global $CFG, $USER, $DB;
	
	$output = '';  // Build up the page
	
	$cm  = $this->cm;   $course = $this->course;    $capdmdwb = $this->capdmdwb;
	$sid = ($this->sid == -1) ? 0 : $this->sid;  // This may be -1
	
        $viewother = false;

        $nm = $USER->firstname." ".$USER->lastname;
        $context = context_module::instance($this->cm->id);
	
        $output .= html_writer::tag('h2', get_string('myworkbook', 'capdmdwb').$nm, array('class' => 'headingblock header'));
	$output .= html_writer::tag('div', '', array('class' => 'dwb-cover'));  // Front Cover
	$output .= html_writer::tag('div', '', array('class' => 'dwb-topic'));  // Throws a page
	
        $showlinks = true;
        if ($sid == 0) { $sid = $USER->id; } 
	else {
	    $student = $DB->get_record('user', array('id' => $sid), '*', MUST_EXIST);   $showlinks = false; 
	}
        // This is just to ensure that we use the DWB variable as the user name.  Might
        // be set by the tutor looking at someone else's DWB
	
        $arrFields = array();    $arrEntries = array();
	$userid = $USER->id;

        $strSQL = "SELECT act.id, wrap.topic_no, wrap.session_no, act.course, wrap.mod_id, wrap.wrapper_id, act.activity_id, act.data_type, act.qpart AS qpart, wrap.title, wrap.preamble, act.run_order, resp.data_id AS data_id, resp.data_value AS data_value, resp.data_option AS data_option, resp.data_explanation AS data_explanation, resp.response_include AS response_include FROM {capdmdwb_activity} act LEFT JOIN (SELECT data_id, data_value, data_option, data_explanation, data_type, response_include FROM {capdmdwb_response} WHERE course = ".$course->id." AND user_id = ".$userid.") resp ON act.activity_id = resp.data_id LEFT JOIN {capdmdwb_wrapper} wrap ON act.wrapper_id = wrap.wrapper_id AND wrap.course=act.course WHERE wrap.course = ".$course->id." AND wrap.role_id='".$this->dwbrole."' ORDER BY wrap.topic_no, wrap.session_no, act.run_order";

        $rs = $DB->get_recordset_sql($strSQL);
	
	if (!$rs->valid()) {
	    $output .= html_writer::tag('h1', get_string('noworkbook','capdmdwb'), array('id'=>'noworkbook'));
	} else {
	    // Collate the actual output in this variable then put ut at end
      	    $dwb_summary = html_writer::start_tag('div', array('id' => 'summary_detail', 'class' => 'dwb-detail'));  // Div S1 +
      	    $dwb_summary .= html_writer::start_tag('ul', array('class' => 'dwb-boxes'));
	    
	    $lastwrapper = ""; $lastactivity = ""; $sameactivity = 0;
	    $odd_even = " dwb-group-odd"; $item_highlight = "";
	    $strTopic = get_string('topic','capdmdwb');
	    
	    $responses = array(); $responsecounts = array();  // Keep a tally of how many activities there are in a task

	    // Go through the rows building up the DWB Summary
	    $lastactivitygroup = 1;    $odd_even = ' dwb-group-odd';  $done = 0;  $notdone = 0;  
	    foreach ($rs as $row){
		if ($lastactivity == $row->wrapper_id)  // Seen this so don't count it again (MRQ?), but count activities in task
		    $sameactivity += 1;
		else {
		    $lastactivity = $row->wrapper_id;  $sameactivity = 0;  // Reset counter
		}
		
		// need to do this on the first pass through so there is a value for lastactivitygroup
		if ($lastactivitygroup != $row->session_no){
		    $odd_even = ($odd_even == ' dwb-group-odd') ? ' dwb-group-even' : ' dwb-group-odd';
		}
		
		$tabid = $row->topic_no;
		if ($lastwrapper == $row->topic_no) {  // We are in the same topic as before
		    if (strlen($row->data_value) != 0) {  // Has been filled in
			$dwb_summary .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-square', 'style' => 'font-weight: bold;')), array('class' => 'dwb-notdone'.$odd_even));
			$done++;
		    } else {
			$dwb_summary .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-square-o', 'style' => 'font-weight: bold;')), array('class' => 'dwb-notdone'.$odd_even));
			$notdone++;
		    }
		} else {
		    $dwb_summary .= html_writer::end_tag('ul');  // Into a new topic so close the UL off
		    $dwb_summary .= $strTopic.' '.$tabid;  // The word Topic
		    $dwb_summary .= html_writer::start_tag('ul', array('class' => 'dwb-boxes'));
		    
		    if (strlen($row->data_value) != 0) {
			$dwb_summary .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-square', 'style' => 'font-weight: bold;')), array('class' => 'dwb-notdone'.$odd_even));
			$done++;
		    } else {
			$dwb_summary .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-square-o', 'style' => 'font-weight: bold;')), array('class' => 'dwb-notdone'.$odd_even));
			$notdone++;
		    }
		}
		
		$lastwrapper = $row->topic_no;    $lastactivitygroup = $row->session_no;  // Update some pointers

//		if (strlen($row->data_value) > 0) {
		    if ($row->data_type != "select") {  // use wrapper ID for select as Activity ID is different 
			array_push($responses, array($row->topic_no, $row->session_no, $row->data_id, $row->data_value, $row->data_option, $row->data_explanation, $row->qpart, $row->data_type, $row->activity_id, $row->title, $row->preamble));
			$responsecounts[$row->activity_id] = $sameactivity;  // Keep a count
		    }
		    else {
			array_push($responses, array($row->topic_no, $row->session_no, $row->data_id, $row->data_value, $row->data_option, $row->data_explanation, $row->qpart, $row->data_type, $row->wrapper_id, $row->title, $row->preamble));
			$responsecounts[$row->wrapper_id] = $sameactivity;  // Keep a count
		    }
//		}
	    }

	    // Close off the summary section
	    $dwb_summary .= html_writer::end_tag('ul').html_writer::end_tag('div');  // Div 1 -
	    	    

	    // Some constants. Build up an array of arrays of responses
	    define ('_TOPICNO', 0);	    define ('_SESSNO', 1);	    define ('_DATAID', 2);    // 0 = Topic No; 1 = Session no; 2 = Data ID (Response ID)
	    define ('_DATAVAL', 3);	    define ('_DATAOPT', 4);	    define ('_DATAEXP', 5);   // 3 = Data Value; 4 = Data Option; 5 = Data Explanation
	    define ('_QPART', 6);	    define ('_DATATYPE', 7);	    define ('_ACTID', 8);     // 6 = QPart; 7 = Data Type; 8 = Activity ID
	    define ('_TITLE', 9);	    define ('_PREAMB', 10);                                   // 9 = Title; 10 = Preamble

	    // Set up to assume there is no Topic 0, which tends to be a preamble.  Adjust accordingly
	    $dwb_topics = array();
	    $topic0 = 1;  $lastTopic = -99;  $lastSession = 0;  $sameactivity = 0;  $i = 1; $lastactivity = "";  // Used to suppress repeated qparts, e.g. for MRQs
	    
	    foreach ($responses as $response) {  // Go through all the responses, looking at the detail
		$thisResponse = '';

		// First check to see if this is a new topic
		if ($lastTopic != $response[_TOPICNO]) {  // Topic No in position 0
		    if ($lastTopic != -99) {
			$dwb_topics[$i] .= html_writer::end_tag('div');  // Div 1
//echo("<p>END 1</p>");		    
		    }

		    $i = $response[_TOPICNO];             // Query is ordered by Topic No so always increases, but not necessarily by 1
		    if ($i == 0) $topic0 = 0;      // used later to put out tabs

		    $dwb_topics[$i] = html_writer::start_tag('div', array('class' => 'dwb-topic'));  // Add a new element with the key = $i Div 1
//echo("<p>START 1</p>");		    
		}

		// Now is this an activity that we've seen before.  Selects have the same wrapper ID but different Activity IDs
		if ($lastactivity == $response[_ACTID])  // Seen this so don't count it again (MRQ?), but count the part occurrences
		    $sameactivity += 1;
		else {
		    $lastactivity = $response[_ACTID];  $sameactivity = 0;
		}

		// if there is no data_id then display a message accordingly
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
			$thisResponse = html_writer::tag('span', '&nbsp;', array('class' => 'dwb-radio-icon')).html_writer::tag('span', $this->utf_hexup($response[_DATAEXP]), array());
			break;
		    case "select":		
			$thisResponse = html_writer::tag('span', '&nbsp;', array('class' => 'dwb-select-icon')).html_writer::tag('span', $this->utf_hexup($response[_DATAOPT]), array());
			break;
		    case "mansopt":
			$thisResponse = html_writer::tag('span', '&nbsp;', array('class' => 'dwb-radio-icon')).html_writer::tag('span', htmlspecialchars($this->utf_hexup($response[_DATAEXP])), array());
			break;
		    case "mrq":
			$thisResponse = html_writer::tag('span', '&nbsp;', array('class' => 'dwb-checkbox-icon')).html_writer::tag('span', $this->utf_hexup($response[_DATAEXP]), array());
			break;
		    }
		}

		// New Session
		if ($response[_SESSNO] != $lastSession) {  // Put up a new Title
		    $dwb_topics[$i] .= html_writer::tag('div', $response[_TITLE], array('class' => 'dwb-detail-group-title'));  // Div +/-
		}
		
//		$dwb_topics[$i] .= html_writer::tag('a', '', array('name' => $response[_ACTID]));  // Prepare to open Div 2

		if ($sameactivity == 0) {  // Opens a DIV for a new activity
		    if (strlen($response[_PREAMB]) > 0) {  // Is there a Preamble
			$dwb_topics[$i] .= html_writer::tag('div', $response[_PREAMB], array('class' => 'dwb-detail-group-preamble'));  // Div +/-
		    }

		    $dwb_topics[$i] .= html_writer::start_tag('div', array('id' => 'dwb-detail_'.$response[_DATAID], 'class' => 'dwb-response-qpart'));  // Div 2 +
//echo("<p>START 2A</p>");		    

		    $dwb_topics[$i] .= html_writer::tag('div', $response[_QPART], array('id' => 'dwb-detail-expander_'.$response[_DATAID], 'class' => 'dwb-qpart-expander', 'style' => 'white-space: pre-wrap'));  // QPart Div +/-
		}
		else {
		    if ($response[_DATATYPE] == 'select') {  // If this is a select then there is a seperate QPart
			$dwb_topics[$i] .= html_writer::start_tag('div', array('id' => 'dwb-detail_'.$response[_DATAID], 'class' => 'dwb-response-qpart'));  // Div 2+ (alt)
//echo("<p>START 2B</p>");		    
			$dwb_topics[$i] .= html_writer::tag('div', $response[_QPART], array('id' => 'dwb-detail-expander_'.$response[_DATAID], 'class' => 'dwb-qpart-expander', 'style' => 'white-space: pre-wrap'));  // QPart Div +/-
		    }
		    else  {
			$dwb_topics[$i] .= html_writer::start_tag('div', array('id' => 'dwb-detail_'.$response[_DATAID]));  // Div 2+ (alt)
//echo("<p>START 2C</p>");		    
		    }
		}

		$dwb_topics[$i] .= html_writer::end_tag('div');  // Div 2 -
//echo("<p>END 2</p>");		    
		$dwb_topics[$i] .= html_writer::tag('div', $thisResponse, array('class' => 'dwb-response-detail')); // Div +/-

//echo("<p>SAME is ".$sameactivity.", COUNTS are ".$responsecounts[$response[_ACTID]]." for RESPONSE  ".$response[_ACTID]."</p>");
		if ($sameactivity == $responsecounts[$response[_ACTID]]) {  // Are we at the last of a set?
//		    $dwb_topics[$i] .= html_writer::end_tag('div');  // Closes all divs
		}
		
		$lastTopic = $response[_TOPICNO];	$lastSession = $response[_SESSNO];
	    }  // Foreach

//	    $dwb_topics[$i] .= html_writer::end_tag('div');  // Closes all divs
//	    $dwb_topics[$i] .= html_writer::end_tag('div');  // Closes all divs
//	    $dwb_topics[$i] .= html_writer::end_tag('div');  // Closes all divs

	    
	    // Now the Tabs
	    $instruction = get_string('instruction','capdmdwb');    $str_summary = get_string('summarytab','capdmdwb');
//	    $output .= html_writer::start_tag('div', array('id' => 'dwb-details'));  // Div 1 +
//	    $output .= html_writer::tag('p', $instruction);
/*
	    $output .= html_writer::start_tag('ul');
	    $output .= html_writer::tag('li', html_writer::tag('a', $str_summary ,array('href' => '#summary')));

	    // Tabs may start at 0.  Only put put tabs with content
	    for ($i=$topic0; $i<=$tabid; $i++) {  // Output the tab informaton with the topic title
		if (array_key_exists($i, $dwb_topics) == true) 
		    $output .= html_writer::tag('li', html_writer::tag('a', $strTopic.' '.$i, array('href' => '#xtab-'.$i)));
	    }
	    $output .= html_writer::end_tag('ul');
*/
	    // The Details.  Again only these with content.
	    $output .= html_writer::start_tag('div');  // Div 2 +

	    $output .= html_writer::start_tag('div', array('id' => 'summary'));  // Div 3 +
	    $output .= $dwb_summary;
	    $output .= html_writer::end_tag('div');  // Div 3 -

//		    $output .= html_writer::tag('p', "KWC KWC KWC KWC");
	    for ($i=$topic0; $i<=$tabid; $i++) {
		if (array_key_exists($i, $dwb_topics) == true) {
//		    $output .= html_writer::start_tag('div', array('id' => '#xtab-'.$i));  // Div 3 +
		    $output .= $dwb_topics[$i];
//		    $output .= html_writer::end_tag('div');  // Div 3 -
		}
	    }
//		    $output .= html_writer::tag('p', "KWC KWC KWC KWC");
	    
	    $output .= html_writer::end_tag('div');  // Dive 2 --
	    $output .= html_writer::end_tag('div');  // Dive 1 --
	}
	
	$output .= $this->capdmdwb_key();  // This prints the key at the bottom.

	return $output;
    }

    private function capdmdwb_key() {
    // ----------------------------

	$output = '';
	$output .= html_writer::start_tag('ul', array('id' => 'dwb-key', 'class' => 'dwb-boxes'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-square-o', 'style' => 'font-weight: bold;')).get_string('box_notdone', 'capdmdwb'), 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-square', 'style' => 'font-weight: bold;')).get_string('box_done', 'capdmdwb'), 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-square dwb-highlight', 'style' => 'font-weight: bold;')).get_string('box_highlight', 'capdmdwb'), 
				array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::end_tag('ul');

	return $output;
    }
    
} // End of Class