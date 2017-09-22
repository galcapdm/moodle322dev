<?php

/**
 * DWB type class for the planner type.
 *
 * @package    dwbtype
 * @subpackage planner
 * @copyright  CAPDM 17-Jun-2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
    

defined('MOODLE_INTERNAL') || die();


/**
 * The planner dwb type.
 *
 * @copyright  CAPDM
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dwb_planner extends dwb_type {
// =================================

    private $dwbrole = "planner";
    private $likert_range = 3;  // The default, but could be 5
    private $studentlist;
    private $max_topic_count = 0, $max_activity_count = 0;

    private $GREEN  = "#00aa00";
    private $ORANGE = "#ffaa00";
    private $RED    = "#aa0000";
    private $GREY1  = "#aaaaaa";
    private $GREY2  = "#dddddd";
    private $YELLOW = "#ffff00";

    private function utf_hexup($str) {
        return preg_replace("/%u(....)/", "&#x$1;", $str);
    }
    
    /**
     * @return nothing
     */
    public function render() {
    // -----------------------
	
	global $CFG, $PAGE, $DB, $USER, $COURSE;

        $PAGE->requires->jquery();  // We use the DataTables library which needs JQuery
        $PAGE->requires->js_call_amd('mod_capdmdwb/dwb', 'init', null);
	
	// What has been passed through?
	$cm  = $this->cm;   $course = $this->course;    $capdmdwb = $this->capdmdwb;
	$sid = ($this->sid == -1) ? $USER->id : $this->sid; // These two params may be -1
	$tid = ($this->tid == -1) ? 1 : $this->tid;  
	$tabid = $this->tabid;

	// =============== The Planner DWB ===========================
        $context = context_module::instance($this->cm->id);


	$output = '';  // Build up the page

        $output .= html_writer::tag('h1', html_writer::tag('i', '', array('class' => 'fa fa-list', 'style' => 'font-weight: bold;')).
				    '&nbsp;'.get_string('myplanner', 'capdmdwb'), 
				    array('class' => 'dwb-header'));

	$output .= html_writer::tag('p', get_string('planners', 'capdmdwb'));	    

	// First work out how many topics we have
	$this->max_topic_count = $DB->get_field_sql("SELECT MAX(topic_no) FROM {capdmdwb_wrapper} WHERE role_id = 'planner' AND course = :course",
						    array('course' => $COURSE->id));

	// get a list of students enrolled on this course and who have filled in some of the TOT planners
	$this->studentlist = $DB->get_records_sql('SELECT DISTINCT u.id, u.firstname, u.lastname, u.firstaccess, u.lastaccess,u.city, u.country 
                                                   FROM {capdmdwb_response} r 
                                                   INNER JOIN {user} u ON r.user_id = u.id 
                                                   WHERE course = :course ORDER BY u.lastname, u.firstname',
						    array('course' => $COURSE->id));
	    
	// The Tabs
	$output .= html_writer::start_tag('div', array('id' => 'dwb-details'));

	$output .= html_writer::start_tag('ul');

	$atts = array();
	// Tutors have extra tabs
	if (has_capability("mod/capdmdwb:share", $context)) {
	    $atts = array();  
	    if ($tabid == 0) {
		$atts['dwb-tab-index'] = '0';  $atts['class'] = 'dwb-planner-tab-selected';  // Add a class?
	    }
	    $output .= html_writer::tag('li', html_writer::tag('a', get_string('tot_alltopics', 'capdmdwb'), array('href' => '#tot-all')), $atts);
	    $atts = array();  
	    if ($tabid == 1) {
		$atts['dwb-tab-index'] = '1';  $atts['class'] = 'dwb-planner-tab-selected';  // Add a class?
	    }
	    $output .= html_writer::tag('li', html_writer::tag('a', get_string('tot_onetopic', 'capdmdwb'), array('href' => '#tot-one')), $atts);
	}
	$atts = array();  
	if ($tabid == 2) {
	    $atts['dwb-tab-index'] = '2';  $atts['class'] = 'dwb-planner-tab-selected';  // Add a class?
	}
	$output .= html_writer::tag('li', html_writer::tag('a', get_string('tot_studentview','capdmdwb') ,array('href' => '#tot-student')), $atts);
	
	$output .= html_writer::end_tag('ul');
	
	// The Details
	// Tutors have extra tabs
	$output .= html_writer::start_tag('div');

	if (has_capability("mod/capdmdwb:share", $context)) {
	    $atts = array('id' => '#tot-all');  if ($tabid == 0) $atts['class'] = 'dwb-planner-panel-selected';  // Add a class?
	    $output .= html_writer::start_tag('div', $atts);
	    $output .= html_writer::tag('p', $this->capdmdwb_tot_all($cm->id, $COURSE->id));
	    $output .= html_writer::end_tag('div');

	    $atts = array('id' => '#tot-one');  if ($tabid == 1) $atts['class'] = 'dwb-planner-panel-selected';  // Add a class?
	    $output .= html_writer::start_tag('div', $atts);
	    $output .= html_writer::tag('p', $this->capdmdwb_tot_one($cm->id, $COURSE->id, $tid));
	    $output .= html_writer::end_tag('div');
	}
	
	$atts = array('id' => '#tot-student');  if ($tabid == 2) $atts['class'] = 'dwb-planner-panel-selected';  // Add a class?
	$output .= html_writer::start_tag('div', $atts);
	$output .= html_writer::tag('p', $this->capdmdwb_tot_student($cm->id, $COURSE->id, $sid));
	$output .= html_writer::end_tag('div');
	
	$output .= html_writer::end_tag('div').html_writer::end_tag('div');

	return $output;
    }

    private function capdmdwb_tot_all($dwbid, $courseid) {
    // -------------------------------------------------
	global $CFG, $DB;

	$return_val  = html_writer::tag('div', get_string('tot_alltopics', 'capdmdwb'), array('class' => 'dwb_header'));
	$return_val .= html_writer::tag('div', get_string('planner_all', 'capdmdwb').html_writer::empty_tag('p'));

	if ($this->max_topic_count == false) return("No Topics Found!");
	else {
	    $strsql = "SELECT DISTINCT CONCAT(u.user_id, u.activity_id) AS id, u.*, r.data_id, r.data_value 
	    	       FROM (SELECT * FROM (SELECT DISTINCT u.id AS user_id,
		       u.firstname, u.lastname
		       FROM {capdmdwb_response} r INNER JOIN {user} u ON r.user_id = u.id
		       WHERE r.course = :course1 ORDER BY u.lastname, u.firstname) u
		       INNER JOIN (SELECT wrap.course, wrap.topic_no, wrap.session_no, wrap.run_order, wrap.wrapper_id, act.activity_id
		       FROM {capdmdwb_activity} act 
		       INNER JOIN {capdmdwb_wrapper} wrap ON act.wrapper_id = wrap.wrapper_id AND wrap.course=act.course
		       WHERE wrap.course = :course2 AND wrap.role_id='planner' AND act.data_type='mansopt') w ) u
		       LEFT JOIN {capdmdwb_response} r ON u.user_id = r.user_id AND u.activity_id = r.data_id AND u.course = r.course
	               ORDER BY u.user_id, u.topic_no, u.session_no, u.run_order";
	    $tots = $DB->get_records_sql($strsql, array('course1' => $courseid, 'course2' => $courseid));
	    
	    if ($tots == false) return("No Topics Found!");
	    else {
		// Create a table with topic_count + 2 columns
		$table = new html_table();
		$table->id = "capdmdwb_tot_all";  // Identify the table

		// Get the header information
		$head = array();
		$head[] = html_writer::tag('span', get_string('tot_student_col', 'capdmdwb'));
		for ($i=1; $i<=$this->max_topic_count; $i++)  {
		    $head[] = html_writer::tag('a', get_string('topic', 'capdmdwb').$i, 
					       array('text-align' => 'center', 'href' => $CFG->wwwroot.'/mod/capdmdwb/view.php?id='.$dwbid.'&sid='.$this->sid.'&tid='.$i."&tabid=1"));
		}
		$head[] = html_writer::tag('span', get_string('tot_status_col', 'capdmdwb'), array('text-align' => 'center'));
		$table->head = $head;
				
		// Need to keep three counts going: # of Learning Objectives; # completed by student; student score
		$col_count = 1; $lo_count = 0;  $lo_all_count = 0;  $lo_done = 0;  $lo_all_done = 0;  $lo_score = 0;  $lo_all_score = 0;
		$row_cells = array();  $last_student = -1;  $last_lo = -1; $tind = 0;  // Topic index
		$table->data = array();  // Now fill in the students
		
		foreach($tots as $t) {
		    if ($t->user_id != $last_student) {
			if ($last_student != -1) {  // Add in the row of data
			    // First, add up the overall score
			    $row_cells[] = $this->capdmdwb_tot_all_style($lo_done, $lo_count, $lo_score);  
			    $row_cells[] = $this->capdmdwb_tot_all_score($lo_all_score, $lo_all_done);
			    $student_row->cells = $row_cells;  array_push($table->data, clone $student_row);  // Add the cells to the row, row to table
			    $col_count++;
			}

			$last_student = $t->user_id;  // Update pointers and counters
			$tind = 0;  $lo_score = 0;  $lo_count = 0;  $lo_all_score = 0;  $lo_all_count = 0;  $lo_done = 0;  $lo_all_done = 0;  $last_lo = -1;  // Reset all counters

			$row_cells = array();  $student_row = new html_table_row();  // Empty and reuse.  Add the Student name in the first column
			$row_cells[] = new html_table_cell(html_writer::tag('a', $t->firstname." ".$t->lastname, 
									    array('href' => $CFG->wwwroot.'/mod/capdmdwb/view.php?id='.$dwbid.'&sid='.$t->user_id.'&tid='.$this->tid."&tabid=2")));
		    }

		    if ($t->topic_no != $last_lo) {  // Moved to a new topic?
			if ($last_lo != -1) {
			    $row_cells[] = $this->capdmdwb_tot_all_style($lo_done, $lo_count, $lo_score);

			    $col_count++; $lo_count = 0;  $lo_done = 0; $lo_score = 0; // Reset for next topic
			}
			
			$last_lo = $t->topic_no;  // Update the pointer
		    }

		    // Do the totalling work
		    $lo_score = $lo_score + $t->data_value;  $lo_all_score = $lo_all_score + $t->data_value;  // Running scores
		    $lo_count = $lo_count + 1;  $lo_all_count = $lo_all_count + 1;
		    if ($t->data_value != null) {
			$lo_done = $lo_done + 1;  $lo_all_done = $lo_all_done + 1;
		    }
		    //echo("<p>Student is ".$t->user_id.", LO is ".$t->data_id.", Score = ".$t->data_value." SCORE is ".$lo_score.", COUNT is ".$lo_count.", DONE is ".$lo_done."</p>");
		}
		
		// Finally add in the last student
		$row_cells[] = $this->capdmdwb_tot_all_style($lo_done, $lo_count, $lo_score);  $row_cells[] = $this->capdmdwb_tot_all_score($lo_all_score, $lo_all_done);
		$student_row->cells = $row_cells;  array_push($table->data, clone $student_row);  // Add the cells to the row, row to table
		$col_count++;
	    }
	    
	    $return_val .= $this->capdmdwb_filter($col_count);  // This prints the TOT Student status filter
	    $return_val .= html_writer::table($table);
	}

	$return_val .= $this->capdmdwb_tot_student_key();  // This prints the TOT Student key at the bottom.

	return $return_val;
    }
    
    private function capdmdwb_tot_one($dwbid, $courseid, $topicid) {
    // -----------------------------------------------------------

	global $CFG, $DB;

	$max_activity_count = 0;  
	$return_val  = html_writer::tag('div', get_string('tot_onetopic', 'capdmdwb').' - '.get_string('topic', 'capdmdwb').$topicid, array('class' => 'dwb_header'));
	$return_val .= html_writer::tag('div', get_string('planner_one', 'capdmdwb'));

	// Now work out how many topic objectives we have
	$max_activity_count = $DB->get_field_sql("SELECT COUNT(a.activity_id) FROM {capdmdwb_wrapper} w 
                                                        INNER JOIN {capdmdwb_activity} a ON w.wrapper_id = a.wrapper_id AND w.course = a.course
                                                        WHERE w.role_id = 'planner' AND w.course = ".$courseid." AND w.topic_no = ".$topicid);
	if ($max_activity_count == false) return("No Activities Found!");
	else {
	    $strsql = "SELECT DISTINCT CONCAT(u.user_id, u.activity_id) AS id, u.*, r.data_id, r.data_value FROM (SELECT * FROM (SELECT DISTINCT u.id AS user_id, u.firstname, u.lastname
                       FROM {capdmdwb_response} r INNER JOIN {user} u ON r.user_id = u.id 
                       WHERE r.data_type = 'mcq' AND r.course = :course1 ORDER BY u.lastname, u.firstname) u
                       INNER JOIN (SELECT wrap.course, wrap.topic_no, wrap.session_no, wrap.run_order, wrap.wrapper_id, act.activity_id 
                       FROM {capdmdwb_activity} act INNER JOIN {capdmdwb_wrapper} wrap ON act.wrapper_id = wrap.wrapper_id AND wrap.course=act.course
                       WHERE wrap.topic_no = :topic AND wrap.course = :course2 AND wrap.role_id='planner') w ) u
                       LEFT JOIN {capdmdwb_response} r ON u.user_id = r.user_id AND u.activity_id = r.data_id AND u.course = r.course
                       ORDER BY u.user_id, u.topic_no, u.session_no, u.run_order";

	    $tots = $DB->get_records_sql($strsql, array('course1' => $courseid, 'topic' => $topicid, 'course2' => $courseid));
	    
	    if ($tots == false) return("No Topics Found!");
	    else {
		// Create a table with activity_count + 2 columns
		$table = new html_table();
		$table->id = "capdmdwb_tot_one";
 
		$head = array();
		$head[] = html_writer::tag('span', get_string('tot_student_col', 'capdmdwb'));
		for ($i=1; $i<=$max_activity_count; $i++) 
		    $head[] = html_writer::tag('span', get_string('tot_objective_col', 'capdmdwb').$i, array('text-align' => 'center'));
		$head[] = html_writer::tag('span', get_string('tot_status_col', 'capdmdwb'));
		$table->head = $head;
		
		// Need to keep three counts going: # of Learning Objectives; # completed by student; student score
		$row_cells = array();  $last_student = -1;  $tind = 0;  // Topic index
		$lo_count = 0;  $lo_all_count = 0;  $lo_done = 0;  $lo_score = 0;  $lo_all_score = 0;
		$student_row = new html_table_row();
		$table->data = array();  // Now fill in the students
		
		foreach($tots as $t) {
		    if ($t->user_id != $last_student) {
			if ($last_student != -1) {  // Add in the row of data
			    // First, add up the overall score
			    $row_cells[] = $this->capdmdwb_tot_one_score($lo_done, $max_activity_count, $lo_score);
			    $student_row->cells = $row_cells;  array_push($table->data, clone $student_row);  // Add the cells to the row, row to table
			}

			$last_student = $t->user_id;  // Update pointers and counters
			$tind = 0;  $lo_done = 0;  $lo_score = 0;  // Reset all counters

			$row_cells = array();  $student_row = new html_table_row();  // Empty and reuse.  Add the Student name in the first column
			$row_cells[] = new html_table_cell(html_writer::tag('a', $t->firstname." ".$t->lastname, 
									    array('href' => $CFG->wwwroot.'/mod/capdmdwb/view.php?id='.$dwbid.'&sid='.$t->user_id.'&tid='.$this->tid."&tabid=2")));
		    }

		    // Add in a Learning Objective value
		    $row_cells[] = $this->capdmdwb_tot_one_style($t->data_value);

		    // Do the totalling work
		    if ($t->data_value != '') {
		        $lo_score = $lo_score + $t->data_value;  $lo_done = $lo_done + 1;  // Only count top notch replies
		    }
		}
		
		// Finally add in the last student
		$row_cells[] = $this->capdmdwb_tot_one_score($lo_done, $max_activity_count, $lo_score);
		$student_row->cells = $row_cells;  array_push($table->data, clone $student_row);  // Add the cells to the row, row to table
		
		$return_val .= html_writer::table($table);
	    }

	    $return_val .= $this->capdmdwb_tot_student_key();  // This prints the TOT Student key at the bottom.
	    
	    return $return_val;
	}
    }
    
    private function capdmdwb_tot_student($dwbid, $courseid, $userid) {
    // --------------------------------------------------------------

	global $DB, $USER;

	$this->max_activity_count = 0;  // Delay the header till we know who the student is

	// Now work out how many topic objectives we have
	$strsql = "SELECT w.topic_no, COUNT(a.activity_id) AS actcount
                   FROM {capdmdwb_wrapper} w INNER JOIN {capdmdwb_activity} a ON w.course = a.course AND w.wrapper_id = a.wrapper_id
                   WHERE w.course = :course AND w.role_id = 'planner' AND a.data_type = 'mansopt'
                   GROUP BY w.topic_no ORDER BY w.topic_no";
	$activity_count = $DB->get_records_sql($strsql, array('course' => $courseid));

	if ($activity_count == false) return("No Activities Found!");
	else {
            $acts = array();   $this->max_activity_count = 0;  $min_topic_no = 99; $prev_activity_count = 0;  // Is it right to assume 0?
	    foreach ($activity_count as $a) {
		if (($a->topic_no - $prev_activity_count) > 1) {  // a gap in the sequence
		    while ($prev_activity_count < ($a->topic_no - 1)) {
			$prev_activity_count++;
			$acts[$prev_activity_count] = 0;  // Fill the count with 0
		    }
		}

		$acts[$a->topic_no] = $a->actcount;  // Keep an array of how many activities are in this particular topic
		if ($a->topic_no < $min_topic_no) $min_topic_no = $a->topic_no;
 	        if ($a->actcount > $this->max_activity_count) $this->max_activity_count = $a->actcount; 

		$prev_activity_count = $a->topic_no;  // Up this pointer
	    }
		
	    // Who is our student?
	    $st = $DB->get_record('user', array('id' => $userid));
	    if ($st == false) return("Student ".$userid." not found!");
	    else {
		// Put out the header along with the student name
		$return_val  = html_writer::tag('div', get_string('tot_studentview', 'capdmdwb').' - '.$st->firstname.' '.$st->lastname, 
						array('class' => 'dwb_header'));
		$return_val .= html_writer::tag('div', get_string('planner_student', 'capdmdwb'));

		// Create a table with activity_count + 2 columns
		$table = new html_table();
		$table->id = "capdmdwb_tot_student";
		
		// Get the header information
		$head = array();
		$head[] = html_writer::tag('span', get_string('tot_topic_col', 'capdmdwb'));
		for ($i=1; $i<=$this->max_activity_count; $i++) 
		    $head[] = html_writer::tag('span', get_string('tot_objective_col', 'capdmdwb').$i, array('align' => 'text-center'));
		$head[] = html_writer::tag('span', get_string('tot_complete_col', 'capdmdwb'), array('text-align' => 'center'));
		$table->head = $head;

		// Now fill in the topic rows
		$table->data = array();  // Now fill in the students
		$topic_row = new html_table_row();
		
		// Get our student's records
		$strsql = "SELECT act.id, wrap.topic_no, wrap.session_no, resp.data_id AS data_id, resp.data_value AS data_value, 
                           wrap.wrapper_id, act.activity_id, act.data_type,act.run_order
                           FROM {capdmdwb_activity} act 
                           LEFT JOIN (SELECT data_id, data_value
                           FROM {capdmdwb_response} 
                           WHERE course = :course1 AND user_id = :user) resp ON act.activity_id = resp.data_id 
                           LEFT JOIN {capdmdwb_wrapper} wrap ON act.wrapper_id = wrap.wrapper_id AND wrap.course=act.course 
                           WHERE wrap.course = :course2 AND wrap.role_id='planner' 
                           ORDER BY wrap.topic_no, wrap.session_no, act.run_order";

		$rs = $DB->get_recordset_sql($strsql, array('course1' => $courseid, 'user' => $userid, 'course2' => $courseid));

		if (!$rs->valid()) {
  	            $output .= html_writer::tag('h2', get_string('notot','capdmdwb'), array('id'=>'notot'));
		}
		else {
		    // build up an array topic and learning objective responses.  Start from the min topic no
		    for ($i=$min_topic_no; $i<=$this->max_topic_count; $i++)
			for ($j=0; $j<$this->max_activity_count; $j++)
			    $resp[$i][$j] = ($j < $acts[$i]) ? -1 : -99;   // An empty topic array with -99 => no activity, -1 => not attempted 

		    $last_topic = -1;  $tind = 0;  // An index into the Learning Objectives
		    $xll = array('dummy');         // Will want to link back to TOT activity. position 0 not used
		    foreach ($rs as $row) {
			if ($row->topic_no != $last_topic) {
			    if (($row->topic_no - $last_topic) > 1) {  // Again, a gap in the sequence?
				while ($last_topic < ($row->topic_no - 1)) {
				    $last_topic++;
				    $xll[$last_topic] = '';  // Fill the link with an empty string
				}
			    }

			    $xll[] = $row->wrapper_id;     // This is our link back for each row entry
			    $last_topic = $row->topic_no;  // Update our counter
			    $tind = 0;
			}

			// Add in the detail of this row
			if ($row->data_type == 'mansopt') {  // This probably not needed - in the query
			    if ($row->data_value != null)
				$resp[$row->topic_no][$tind] = $row->data_value;  // Add in the detail, if not null
			    $tind++;
			}		    
		    }
		    // We know how many topics max there are
		    for ($i=1; $i<=$this->max_topic_count; $i++) {
			$row_class = '';
			$topic_row = new html_table_row();  $row_cells = array();

			$row_cells[] = ($xll[$i] == '')
			    ? new html_table_cell(html_writer::tag('a', $i, array('class' => 'dwb-task-link')))  // Don't actually link to anywhere
			    : new html_table_cell(html_writer::tag('a', $i, array('class' => 'dwb-task-link', 'title' => get_string('goto', 'capdmdwb').$xll[$i], 'href' => '../capdmcourse/xllredirect.php?course='.$courseid.'&xll='.$xll[$i])));
			$overall = 0;  $overall_score = 0;  // Keep a tally of how many are successfully completed

			for ($j=0; $j<$this->max_activity_count; $j++)   // OK for a 3 part Likert Scale.  What about 5?
			    switch ($resp[$i][$j]) {
			    case -99: 
				$row_cells[] = new html_table_cell("<i class='fa fa-minus' style='color: ".$this->GREY2."'></i>");
				break;
			    case -1: 
				$row_cells[] = new html_table_cell("<i class='fa fa-pencil-square-o' style='color: ".$this->GREY1."'></i>");
				break;
			    case 0: 
				$overall = $overall + 1;  $row_cells[] = new html_table_cell("<i class='fa fa-star-o' style='color: ".$this->RED."'></i>");
				break;
			    case 1: 
				$overall = $overall + 1;  $overall_score = $overall_score + 1;  // Adds a bit to the score
				$row_cells[] = new html_table_cell("<i class='fa fa-star-half-o' style='color: ".$this->ORANGE."'></i>");
				break;
			    case 2: 
				$overall = $overall + 1;  $overall_score = $overall_score + 2;  // Add to success and score
				$row_cells[] = new html_table_cell("<i class='fa fa-star' style='color: ".$this->GREEN."'></i>");
				break;
			    default:
				$row_cells[] = new html_table_cell("<i class='fa fa-icon-warning-sign' style=".$this->YELLOW."'></i>");
				break;
			    }

			// Now how many have been given a thumbs up?  Overall performance measure
			$param = array('style' => 'color: '.$this->RED);  $message = "<i class='fa fa-star-o'></i>";  $row_class = "xfa-star-o";

			if ($overall_score >= $acts[$i] * 2) { 
			    $param = array('style' => 'color: '.$this->GREEN);  $message = "<i class='fa fa-star'></i>";  $row_class = "xfa-star";
			}
			else if ($overall_score >= $acts[$i]) 
			{ 
			    $param = array('style' => 'color: '.$this->ORANGE);  $message = "<i class='fa fa-star-half-o'></i>";  $row_class = "xfa-star-half-o";
			}
		    
			$row_cells[] = ($acts[$i] == 0) 
			    ? new html_table_cell("<i class='fa fa-minus' style='color: ".$this->GREY2."'></i>")
			    : new html_table_cell(html_writer::tag('span', $message."&nbsp;".$overall.'/'.$acts[$i], $param));
			$topic_row->attributes['class'] .= $row_class;  // Use the overall status class for possible filtering
			$topic_row->cells = $row_cells;  array_push($table->data, clone $topic_row);  // Add the cells to the row, row to table
		    }
		    
		    $return_val .= html_writer::table($table);
		}
	    }
	}

	$return_val .= $this->capdmdwb_tot_student_key();  // This prints the TOT Student key at the bottom.

	return $return_val;
    }

    private function capdmdwb_tot_all_style($lo_done, $lo_count, $lo_score) {
    // --------------------------------------------------------------------	
	// We need to put out a column entry
	$colv = ($lo_done == 0) ? 0 : round(($this->likert_range-1) * $lo_score / ($lo_done * ($this->likert_range-1)));  // Should it be count, not done?

	$col = $this->GREY1;  $message = "<i class='fa fa-pencil-square-o'></i>";
	if ($lo_done == 0) ; // do nothing 
	else if ($colv == 0) { 
	    $col = $this->RED; $message = "<i class='fa fa-star-o'></i>";
	} else if ($colv == 1) {
	    $col = $this->ORANGE;  $message = "<i class='fa fa-star-half-o'></i>"; 
	} else if ($colv == 2) {
	    $col = $this->GREEN;  $message = "<i class='fa fa-star'></i>";
	}
	
	$c = new html_table_cell($message."&nbsp;".$lo_done."/".$lo_count);  $c->style = 'color: '.$col;
	return $c;
    }

    private function capdmdwb_tot_all_score($lo_all_score,  $lo_all_count) {
    // -------------------------------------------------------------------
	// We need to put out a column entry
//echo("<p>ALL_SCORE is ".$lo_all_score.", ALL_COUNT is ".$lo_all_count."</p>");
//echo("<p>ALL_DIV is ".($lo_all_score / ($lo_all_count * ($this->likert_range-1)))."</p>");
	$colv = ($lo_all_count == 0) ? 0 : round($this->likert_range * ($lo_all_score / ($lo_all_count * ($this->likert_range-1))), 0, PHP_ROUND_HALF_DOWN);  
//echo("<p>ALL COLV is ".$colv.", as ALL SCORE is ".$lo_all_score." and ALL COUNT is ".$lo_all_count."</p>");

	if ($lo_all_count == 0) {  // Don't go further if there has been no rating
	  $col = $this->GREY1;  $message = "<i class='fa fa-pencil-square-o'><span style='display: none;'>dwb-key-blank</span></i>";
        } else if ($colv == 0) { 
	    $col = $this->RED; $message = "<i class='fa fa-star-o'><span style='display: none;'>dwb-key-notc</span></i>";
	} else if ($colv == 1) {
	    $col = $this->ORANGE;  $message = "<i class='fa fa-star-half-o'><span style='display: none;'>dwb-key-some</span></i>";
	} else {
	    $col = $this->GREEN;  $message = "<i class='fa fa-star'><span style='display: none;'>dwb-key-very</span></i>";
	}
	
	$c = new html_table_cell($message);  $c->style = 'color: '.$col;
	return $c;
    }

    private function capdmdwb_tot_one_style($val) {
    // ------------------------------------------	
	// We need to put out a column entry
	$col = $this->GREY1;  $message = "";
	if ($val == 0 && strlen($val) != 0) {
	    $col = $this->RED;  $message = "<i class='fa fa-star-o'></i>";
	} else if ($val == 1) { 
	    $col = $this->ORANGE; $message = "<i class='fa fa-star-half-o'></i>";
	} else if ($val == 2) {
	    $col = $this->GREEN;  $message = "<i class='fa fa-star'></i>"; 
	} else {
	    $col = $this->GREY1;  $message = "<i class='fa fa-pencil-square-o'></i>";
	}
	
	$c = new html_table_cell($message);  $c->style = 'color: '.$col;
	return $c;
    }

    private function capdmdwb_tot_one_score($lo_done, $max_activity_count, $lo_score) {
    // --------------------------------------------------------------------------------
	// We need to put out a final column entry
	$colv = ($lo_done == 0) ? 0 : round(($this->likert_range-1) * ($lo_score / ($lo_done *  ($this->likert_range-1))));  
//echo("<p>DONE is ".$lo_done.", ACT COUNT is ".$max_activity_count.", SCORE is ".$lo_score.", COLV is ".$colv.", DENOM is ".($lo_done / $max_activity_count)."</p>");

	$col = $this->GREY1;  $message = "<i class='fa fa-pencil-square-o'></i>";
	if ($colv == 0 && $lo_done != 0) { 
	    $col = $this->RED; $message = "<i class='fa fa-star-o'></i>";
	} else if ($colv == 1) {
	    $col = $this->ORANGE;  $message = "<i class='fa fa-star-half-o'></i>";
	} else  if ($colv == 2) {
	    $col = $this->GREEN;  $message = "<i class='fa fa-star'></i>";
	}
	
	$c = new html_table_cell($message."&nbsp;".$lo_done."/".$max_activity_count);  $c->style = 'color: '.$col;

	return $c;
    }

    private function capdmdwb_tot_student_key() {
    // ----------------------------------------

	$output = '';
	$output .= html_writer::start_tag('ul', array('id' => 'dwb-key', 'class' => 'dwb-boxes'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-star', 'style' => 'color: '.$this->GREEN.';')).get_string('box_thumbs_up', 'capdmdwb').';&nbsp;', 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-star-half-o', 'style' => 'color: '.$this->ORANGE.';')).get_string('box_hand_o_right', 'capdmdwb').';&nbsp;', 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-star-o', 'style' => 'color: '.$this->RED.';')).get_string('box_thumbs_down', 'capdmdwb').';&nbsp;', 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-pencil-square-o', 'style' => 'color: '.$this->GREY1.';')).get_string('box_pencil', 'capdmdwb').';&nbsp;', 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('i', '', array('class' => 'fa fa-minus', 'style' => 'color: '.$this->GREY2.';')).get_string('box_na', 'capdmdwb'), 
				    array('class' => 'dwb-notdone dwb-group-odd'));
	$output .= html_writer::end_tag('ul');

	return $output;
    }
    
    private function capdmdwb_filter($cols) {
    // ------------------------------------

	$output = '';
	$output .= html_writer::start_tag('ul', array('id' => 'dwb-key', 'class' => 'dwb-boxes'));

	$output .= html_writer::tag('li', get_string('box_filter', 'capdmdwb'), array());

	$output .= html_writer::tag('li', html_writer::tag('a', 
							   html_writer::tag('i', '', array('class' => 'fa fa-th-list', 'style' => 'color: '.$this->GREY2.';')).'All'.';&nbsp;',  
							   array('class' => 'dwb-key-toggle')),
				    array('id' => 'dwb-key-all', 'class' => 'dwb-boxes dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('a', 
							   html_writer::tag('i', '', array('class' => 'fa fa-star', 'style' => 'color: '.$this->GREEN.';')).get_string('box_thumbs_up', 'capdmdwb').';&nbsp;', 
							   array('class' => 'dwb-key-toggle', 'col' => $cols, 'search' => 'dwb-key-very')), 
				    array('id' => 'dwb-key-very', 'class' => 'dwb-boxes dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('a', 
							   html_writer::tag('i', '', array('class' => 'fa fa-star-half-o', 'style' => 'color: '.$this->ORANGE.';')).get_string('box_hand_o_right', 'capdmdwb').';&nbsp;',  
							   array('class' => 'dwb-key-toggle', 'col' => $cols, 'search' => 'dwb-key-some')),
				    array('id' => 'dwb-key-some', 'class' => 'dwb-boxes dwb-group-odd'));
	$output .= html_writer::tag('li', html_writer::tag('a', 
							   html_writer::tag('i', '', array('class' => 'fa fa-star-o', 'style' => 'color: '.$this->RED.';')).get_string('box_thumbs_down', 'capdmdwb').';&nbsp;',  
							   array('class' => 'dwb-key-toggle', 'col' => $cols, 'search' => 'dwb-key-notc')),
				    array('id' => 'dwb-key-notc', 'class' => 'dwb-boxes dwb-group-odd'));
	
	$output .= html_writer::empty_tag('p');  // For a bit of space.
	
	return $output;
    }
    
} // End of Class
