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
class dwb_reflection extends dwb_type {
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
        // =========== The Digital Workbook ===================
	
        $nm = $USER->firstname." ".$USER->lastname;
        $context = context_module::instance($this->cm->id);
	
	//      if (has_capability("mod/capdmdwb:share", $context)) {
	//	$temp .= " (".get_string('teachers', 'capdmdwb')."<a href='students.php?id=".$course->id."&cm_id=".$id."' alt='".get_string('yourstudents', 'capdmdwb')."'>".get_string('studentdwb', 'capdmdwb')."</a>)";
	//      }
        // Suppress the message if this is not the Admin user's DWB
	
        $output .= html_writer::tag('h2', get_string('myworkbook', 'capdmdwb').$nm, array('class' => 'headingblock header'));
//        $output .= html_writer::empty_tag('br');
	
        $showlinks = true;
        if ($sid == 0) { $sid = $USER->id; } 
	else {
	    $student = $DB->get_record('user', array('id' => $sid), '*', MUST_EXIST);   $showlinks = false; 
	}
        // This is just to ensure that we use the DWB variable as the user name.  Might
        // be set by the tutor looking at someone else's DWB
	
        $arrFields = array();    $arrEntries = array();
	
        // are we viewing our own DWB or are we someone with permissions to view someone else's?
        if (has_capability("mod/capdmdwb:share", $context)) {
	    $output .= html_writer::start_tag('div', array('class' => "dwb-tutor"));

	    // get a list of students enorlled on this course
	    $studentlist = $DB->get_records_sql('SELECT distinct u.id, u.firstname, u.lastname, u.firstaccess, u.lastaccess,u.city, u.country FROM {capdmdwb_response} r INNER JOIN {user} u ON r.user_id = u.id WHERE course = '.$course->id.' ORDER BY u.lastname, u.firstname');
	    
	    foreach($studentlist as $s) {
		$arrFields['id'] = $s->id;
		$arrFields['fullname'] = $s->firstname." ".$s->lastname;
		$arrFields['firstaccess'] = date('dS F Y',$s->firstaccess); $arrFields['lastaccess'] = date('dS F Y',$s->lastaccess);
		$arrFields['city'] = $s->city.", ".$s->country;
		$arrFields['dwb_link'] = '<a href="view.php?id='.$_GET['id'].'&selecteddwb='.$s->id.'">'.get_string('viewdwb','capdmdwb').'</a>';
		array_push($arrEntries, $arrFields);
	    }
	    
	    // Build a table for arranging output
	    $table = new html_table();
	    $table->head = array('User ID', 'Name','First access','Last access','City, Country','');
	    $table->data = $arrEntries;
	    
	    $output .= html_writer::start_tag('div', array('class' => "infomessage highlight", 'style' => "margin-bottom: 1em;"));
	    $output .= html_writer::tag('a', get_string('viewstudentlist','capdmdwb'), array('id' => 'toggle_studentlist', 'name' => 'toggle_studentlist'));
	    $output .= html_writer::end_tag('div');
	    
	    $output .= html_writer::start_tag('div', array('id' => 'studentlist', 'class' => 'display-toggle-detail'));
	    
	    $output .= html_writer::tag('a', get_string('viewowndwb','capdmdwb'), array('href' => 'view.php?id='.$_GET['id']));
	    
	    if (sizeof($studentlist) > 0) $output .= html_writer::table($table);
	    else $output .= html_writer::tag('p', get_string('nostudentworkbooks','capdmdwb'), array('class' => 'highlight'));
	    
	    $output .= html_writer::end_tag('div');
	    $output .= html_writer::end_tag('div');  // Tutor list
	}
	
	if ($sid != $USER->id && has_capability("mod/capdmdwb:share", $context)) {
   	    $viewother = true;
	    $nm = $student->firstname." ".$student->lastname;
	    $output .= html_writer::tag('p', get_string('viewinganotherdwb','capdmdwb').
					html_writer::tag('strong', $nm.', '.$student->city.', '.$student->country), 
					array('class' => 'highlight'));
	} else {
	    $viewother = false;
	}

	$output .= html_writer::tag('div', '', array('class' => 'clearfix'));  // Does this sort the newline issue?

	$output .= html_writer::tag('p', get_string('reflections', 'capdmdwb'));	    
	$output .= html_writer::tag('p', get_string('pdfpresummary', 'capdmdwb').
				    html_writer::tag('a', get_string('pdfbooklet', 'capdmdwb'), 
						     array('class' => 'pdfprnicon', 'href' => $CFG->wwwroot.'/mod/capdmdwb/workbook.php?id='.$cm->id.'&dwb='.$sid.'&nm='.urlencode($nm).'&taskticks='.($capdmdwb->taskticks?0:1).'&checksum='.md5($sid.'this is unguessible'), 'target' => '_blank')).get_string('pdfpostsummary', 'capdmdwb'));
	$output .= html_writer::end_tag('div');


	// Are we part of a DWB Grouping?  If so, then add links to your partners
	// ----------------------------------------------------------------------
	$ggid = groups_get_grouping_by_name($course->id, _CAPDMDWB);
	if ($showlinks && $ggid != false) {
      	    if (($g = groups_get_all_groups($course->id, $USER->id, $ggid)) != false) {
		// We are in a group within the DWB grouping, so find out who else is.
		
		$output .= html_writer::start_tag('div', array('id' => 'dwb-groups', 'class' => 'dwb-groups yui3-accordion'));
		
		$output .= html_writer::tag('a', get_string('groups', 'capdmdwb').get_string('share', 'capdmdwb'), 
					    array('href' => 'javascript:void(null);', 'title' => get_string('groups', 'capdmdwb'))); 
		    
		$output .= html_writer::start_tag('div', array('id' => 'dwb-group-item', 'class' => 'yui3-accordion-item'));

		foreach ($g as $gi) {
		    if (groups_is_member($gi->id, $USER->id)) {  // We are in it, who else?
			if (($gms = groups_get_members($gi->id)) != false) {  // We are not alone
			    $output .= html_writer::start_tag('div>');
			    $output .= html_writer::tag('p', get_string('grouping', 'capdmdwb'),
							html_writer::start_tag('a', array('href' => $CFG->wwwroot.'/dwb-forum-'.$course->idnumber.'.xll', 'alt' => get_string('forum', 'capdmdwb'))));
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
			    $output .= html_writer::end_tag('div');
			}
		    }
		}
		
		$output .= html_writer::end_tag('div');
		$output .= html_writer::end_tag('div');  // Close off the hide divs
     	    }
        }
	    
        // NEW LAYOUT START
        // check to see if we are going to display the current user's workbook or are we attempting to see antother user's workbook (must be an admin or similar to do this)
        if (has_capability("mod/capdmdwb:share", $context)) $userid = $sid;
        else $userid = $USER->id;

        $strSQL = "SELECT act.id, wrap.topic_no, wrap.session_no, act.course, wrap.mod_id, wrap.wrapper_id, act.activity_id, act.data_type, act.qpart AS qpart, wrap.title, wrap.preamble, act.run_order, resp.data_id AS data_id, resp.data_value AS data_value, resp.data_option AS data_option, resp.data_explanation AS data_explanation, resp.response_include AS response_include FROM {capdmdwb_activity} act LEFT JOIN (SELECT data_id, data_value, data_option, data_explanation, data_type, response_include FROM {capdmdwb_response} WHERE course = ".$course->id." AND user_id = ".$userid.") resp ON act.activity_id = resp.data_id LEFT JOIN {capdmdwb_wrapper} wrap ON act.wrapper_id = wrap.wrapper_id AND wrap.course=act.course WHERE wrap.course = ".$course->id." AND wrap.role_id='".$this->dwbrole."' ORDER BY wrap.topic_no, wrap.session_no, act.run_order";

        $rs = $DB->get_recordset_sql($strSQL);
	
	if (!$rs->valid()) {
	    $output .= html_writer::tag('h1', get_string('noworkbook','capdmdwb'), array('id'=>'noworkbook'));
	} else {
	    // Start a new variable to put out later
	    $summary_sectoutput = html_writer::start_tag('div', array('id' => 'summary_detail', 'class' => 'dwb-detail'));

	    $summary_slastwrapper = ""; $lastwrapper = ""; $i = 0; $odd_even = " dwb-group-odd"; $item_highlight = "";
	    $strTopic = get_string('topic','capdmdwb');
	    
	    $responses = array();
	    $act_id = (isset($_GET['act_id'])) ? $_GET['act_id'] : 'dummy_XX';
	    
	    $lastactivitygroup = 1;    $odd_even = ' dwb-group-odd';  $done = 0;  $notdone = 0;  
	    foreach ($rs as $row) {
	        $i++;
		
		// Which one do we highlight as selected?
		$item_highlight = (strpos($row->activity_id, $act_id) > -1) ? " dwb-highlight" : '';
		
		// need to do this on the first pass through so there is a value for lastactivitygroup
		if ($lastactivitygroup != $row->session_no)
		    $odd_even = ($odd_even == ' dwb-group-odd') ? ' dwb-group-even' : ' dwb-group-odd';
		
                $tabid = $row->topic_no;
		if ($lastwrapper == $row->topic_no) {
		    if (strlen($row->data_value) != 0) {
			$summary_sectoutput .= html_writer::tag('li', html_writer::tag('a', 
										       html_writer::tag('i', '', array('class' => 'fa fa-square'.$item_highlight, 'style' => 'font-weight: bold;'))
, 
									   array('class' => 'dwb-goto-tab', 'act_id' => $row->activity_id, 'tabid' => $tabid, 
										 'title' => 'Click here to go to this activity')), 
						    array('class' => 'dwb-done'.$item_highlight.$odd_even));
			$done++;
		    } else {
			$summary_sectoutput .= html_writer::tag('li', html_writer::tag('a', 
									   html_writer::tag('i', '', array('class' => 'fa fa-square-o'.$item_highlight, 'style' => 'font-weight: bold;')), 
									   array('class' => 'dwb-goto-tab', 'act_id' => $row->activity_id, 'tabid' => $tabid, 
										 'title' => 'Click here to go to this activity')), 
								array('class' => 'dwb-notdone'.$item_highlight.$odd_even));
			$notdone++;
		    }
		} else {
		    $summary_sectoutput .= html_writer::end_tag('ul');
		    $summary_sectoutput .= html_writer::tag('span', $strTopic.' '.$tabid.html_writer::start_tag('ul', array('class' => 'dwb-boxes')));

		    if (strlen($row->data_value) != 0) {
			$summary_sectoutput .= html_writer::tag('li', html_writer::tag('a', 
										       html_writer::tag('i', '', array('class' => 'fa fa-square'.$item_highlight, 'style' => 'font-weight: bold;')), 
									   array('class' => 'dwb-goto-tab', 'act_id' => $row->activity_id, 'tabid' => $tabid, 
										 'title' => 'Click here to go to this activity')), 
						    array('class' => 'dwb-done'.$item_highlight.$odd_even));
			$done++;
		    } else {
			$summary_sectoutput .= html_writer::tag('li', html_writer::tag('a', 
										       html_writer::tag('i', '', array('class' => 'fa fa-square-o'.$item_highlight, 'style' => 'font-weight: bold;')), 
									   array('class' => 'dwb-goto-tab', 'act_id' => $row->activity_id, 'tabid' => $tabid, 
										 'title' => 'Click here to go to this activity')), 
								array('class' => 'dwb-notdone'.$item_highlight.$odd_even));
			$notdone++;
		    }
		}
		
		$lastwrapper = $row->topic_no;	$lastactivitygroup = $row->session_no;
		
		// Build up an array of arrays of responses
		// 0 = Topic No               1 = Session no            2 = Data ID (Response ID)
		// 3 = Data Value             4 = Data Option           5 = Data Explanation
		// 6 = QPart                  7 = Data Type             8 = Activity ID
		// 9 = Title                 10 = Preamble
		array_push($responses, array($row->topic_no, $row->session_no, $row->data_id, $row->data_value, $row->data_option, $row->data_explanation, $row->qpart, $row->data_type, $row->activity_id, $row->title, $row->preamble));
	    }
	    
	    if ($notdone == 0) {
		// look up the module instance for this course i.e. does it have a certificate, but first you need to find the modid of capdmcert
		$modid = $DB->get_field('modules', 'id',array('name' => 'capdmcert'));
		$modinst = $DB->get_field('course_modules', 'id', array('course' => $course->id, 'module' => $modid));
		if ($modinst) {
		    $output .= html_writer::empty_tag('img', array('src' => $CFG->wwwroot.'/theme/'.current_theme().'/pix/congratulations.jpg'));
		    $output .= html_writer::tag('p', html_writer::tag('a', get_string('congratulations','capdmdwb'), 
								      array('href' => $CFG->wwwroot.'/mod/capdmcert/view.php?id='.$modinst)), 
						array('class' => 'congratulations'));
		}
	    }
	    
	    // Set up to assume there is no Topic 0, which tends to be a preamble.  Adjust accordingly
	    $lastTopic = 1;  $lastSession = 0;  $topic0 = 1; $i = 1;  $arrResponseDetail = array(); //array_fill(1, 9999, '');
	    
	    foreach ($responses as $response) {  // Go through all the responses, looking at the detail
		//$response = explode("|",$response);
		if ($lastTopic != $response[0]) {  // Topic No in position 0
		    $i = $response[0];       // Query is ordered by Topic No so always increases, but not necessarily by 1
		    $arrResponseDetail[$i] = '';  // Add a new element with the key = $i

		    if ($i == 0) $topic0 = 0;  // used later to put out tabs
		}

		$thisResponse = $response[2];  // Data ID
		// if there is no data_id then display a message accordingly
		if (strlen($thisResponse) == 0) {
		    $thisResponse = html_writer::tag('span', get_string('noresponse','capdmdwb'), array('class' => 'highlighttext'));
		} 
		else {
		    // now check what type of response it is
		    switch ($response[7]) {
		    case "textarea":
//			$thisResponse = '<form class="dwb-activity" name="demo1_dwb0101_form"><input type="hidden" name="dwb-instance" value="demo1_dwb0101_fib"><input type="hidden" name="dwb-format" value="textarea"><textarea class="dwb-textarea" id="demo1_dwb0101_fib" rows="3" cols="80">'.nl2br(htmlspecialchars($response[3])).'</textarea></form>';
			$thisResponse = nl2br(htmlspecialchars($response[3]));
			break;
		    case "textbox":
//<input type="hidden" name="dwb-instance" value="demo1_dwb0201_fib"><input type="hidden" name="dwb-format" value="textbox"><input value="'.nl2br(htmlspecialchars($response[3])).'" type="text" class="dwb-textbox" id="demo1_dwb0201_fib" size="25"></form>';
	 	        $thisReponse = nl2br(htmlspecialchars($response[3]));
			break;
		    case "mcq":
			$thisResponse = html_writer::tag('span', 'MCQ'.$this->utf_hexup($response[5]), array('class' => 'dwb-radio-icon'));
			break;
		    case "select":		
			$thisResponse = html_writer::tag('span', "SSS".$this->utf_hexup($response[4]), array('class' => 'dwb-select-icon'));
			break;
		    case "mansopt":
			$thisResponse = html_writer::tag('span', "MMMM".htmlspecialchars($this->utf_hexup($response[5])), array('class' => 'dwb-radio-icon'));
			break;
		    case "mrq":
			$thisResponse = 'MRQ'.html_writer::tag('span', $this->utf_hexup($response[5]), array('class' => 'dwb-checkbox-icon'));
			break;
		    }
		}
		
		$qpartHighlight = ($response[8] == $act_id) ? " dwb-response-qpart-highlight" : "";
		
		if ($response[1] != $lastSession) {
		    $arrResponseDetail[$i] .= html_writer::start_tag('div', array('class' => 'dwb-detail-group-title')).
			html_writer::tag('span', $response[9]).
			html_writer::end_tag('div');
		    if (strlen($response[10]) > 0) {
			$arrResponseDetail[$i] .= html_writer::start_tag('div', array('class' => 'dwb-detail-group-preamble')).
			    html_writer::tag('span', $response[10]).
			    html_writer::end_tag('div');
		    }
		}
		
		$arrResponseDetail[$i] = $arrResponseDetail[$i].
		    html_writer::empty_tag('a', array('name' => $response[8])).
		    html_writer::start_tag('div', array('id' => 'dwb-detail_'.$response[8], 'class' => 'dwb-response-qpart'.$qpartHighlight)).
		    html_writer::start_tag('div', array('id' => 'dwb-detail-expander_'.$response[8], 'class' => 'dwb-qpart-expander', 'style' => 'white-space: pre-wrap')).
		    html_writer::tag('span', $response[6]).
		    html_writer::end_tag('div').
		    html_writer::start_tag('div', array('class' => 'dwb-response-detail')).
		    html_writer::tag('span', $thisResponse).
		    html_writer::end_tag('div').
		    html_writer::start_tag('div', array('class' => 'dwb-response-editlink')).
		    html_writer::tag('a', 'Back to summary', array('href' => '#dwb-details', 'class' => 'dwb-goto-summary'));
		
		// if we are viewing a student record then do not show link back to course
		if ($viewother) {
		    $arrResponseDetail[$i] .= html_writer::end_tag('div').html_writer::end_tag('div');
		} 
		else {
		    $arrResponseDetail[$i] .= ' - '.
			html_writer::tag('a', get_string('editthisactivityentry','capdmdwb'), 
					 array('href' => '../capdmcourse/xllredirect.php?xll='.$response[8])).
			html_writer::end_tag('div').html_writer::end_tag('div');
		}
		
		$lastTopic = $response[0];	$lastSession = $response[1];
	    }
	    
	    $summary_sectoutput .= html_writer::end_tag('ul').html_writer::end_tag('div');
	    
	    // The Tabs
	    $instruction = get_string('instruction','capdmdwb');    $str_summary = get_string('summarytab','capdmdwb');
	    $output .= html_writer::start_tag('div', array('id' => 'dwb-details'));
	    $output .= html_writer::tag('p', $instruction);
	    $output .= html_writer::start_tag('ul');
	    $output .= html_writer::tag('li', html_writer::tag('a', $str_summary ,array('href' => '#summary')));

	    // Tabs may start at 0.  Only put put tabs with content
	    for ($i=$topic0; $i<$tabid+1; $i++) {  // Output the tab informaton with the topic title
		if (array_key_exists($i, $arrResponseDetail) == true) 
		    $output .= html_writer::tag('li', html_writer::tag('a', $strTopic.' '.$i, array('href' => '#tab-'.$i)));
	    }
	    $output .= html_writer::end_tag('ul');
	    
	    // The Details.  Again only these with content.
	    $output .= html_writer::start_tag('div');
	    $output .= html_writer::start_tag('div', array('id' => 'summary'));
	    $output .= html_writer::tag('p', $summary_sectoutput);
	    $output .= html_writer::end_tag('div');

	    for ($i=$topic0; $i < $tabid+1; $i++) {
		if (array_key_exists($i, $arrResponseDetail) == true) {
		    $output .= html_writer::start_tag('div', array('id' => '#tab-'.$i));
		    $output .= html_writer::tag('p', $arrResponseDetail[$i]);
		    $output .= html_writer::end_tag('div');
		}
	    }
	    
	    $output .= html_writer::end_tag('div').
		html_writer::end_tag('div');
	}
	
	$rs->close();

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