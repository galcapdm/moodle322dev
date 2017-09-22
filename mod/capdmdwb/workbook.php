<?php

require_once('../../config.php');
require_once($CFG->libdir.'/filelib.php');
require_once('dwbworkbookbase.php');

$debug = 0;

// Generate a customised Digital Workbook
// CAPDM: 11-Jun-2013

    $id    = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $sid   = optional_param('dwb', 0, PARAM_INT);
    $nm    = optional_param('nm', '', PARAM_TEXT);
    $tt    = optional_param('taskticks', 0, PARAM_INT);

    if ($id) {
        $cm        = get_coursemodule_from_id('capdmdwb', $id, 0, false, MUST_EXIST);

	$course    = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
	$capdmdwb  = $DB->get_record('capdmdwb', array('id' => $cm->instance), '*', MUST_EXIST);
    }
    else die;

    require_login($course->id);

    // Create a Workbook to 'print'
//    $dwbo = "dwb_workbook_".$capdmdwb->role_id;  // Role specifies the type

    // Load the class
    //include_once("type/".$capdmdwb->role_id."/dwbworkbook.php");


    if(!$debug){
        include_once("type/".$capdmdwb->role_id."/dwbworkbook.php");
    } else {



    global $DB;

        $strSQL = "SELECT case when act.data_type = 'mrq' then concat('mrq-', act.id, '-', data_value) else act.id end as id, wrap.title AS topic_title, wrap.user_level, wrap.topic_no, wrap.session_no, act.course, wrap.mod_id,
                   wrap.wrapper_id, act.activity_id, act.data_type, act.qpart, wrap.title, wrap.preamble, wrap.run_order, coalesce(resp.data_id, act.id) as data_id,
                   case data_type when 'mansopt' then
                   coalesce(resp.data_value, -999)
                   else
                   coalesce(resp.data_value, 'Not yet answered')
                   end as data_value, coalesce(resp.data_option, act.id) as data_option,
                   coalesce(resp.data_explanation, 'not yet answered') as data_explanation, resp.response_include
                   FROM {capdmdwb_activity} act
                   LEFT JOIN (SELECT course, data_id, data_value, data_option, data_explanation, response_include
                              FROM {capdmdwb_response}
                              WHERE course = :courseid1 AND user_id = :userid) resp
                   ON act.activity_id = resp.data_id AND act.course = resp.course
                   LEFT JOIN {capdmdwb_wrapper} wrap ON act.wrapper_id = wrap.wrapper_id AND act.course = wrap.course
                   WHERE wrap.course = :courseid2  AND wrap.role_id= :dwbrole
                   ORDER BY wrap.topic_no, wrap.session_no, wrap.run_order";

        $dwb = $DB->get_records_sql($strSQL, array('courseid1'=>7, 'userid'=> 2, 'courseid2'=>7, 'dwbrole'=>'reflection'));

    print_r($dwb);
        // ---------------------------------------------------------

        // ######################################
        // Front page - START
        // ######################################


// ######################################
    // Follow on pages - START
    // ######################################

    // Track the topic number so we know when to display a topic title or not
    $last_topicno = -9999999;
    // MRQ types are difficult to handle as no idea if the next record is part of the mrq
    // need to track data_type and then act accordingly to add top and tail on the html
    // when an MRQ starts and stops!
    $lastType = '';
    $last_wrapperid = '';
    $last_preamble = '';
    $last_qpart = '';
    $mrq = '';
    $mrqHead = '<table cellpadding="5">';
    $mrqFoot = '</table><br /><br />';


    $html = '<style type="text/css">';
    // Pick up custom CSS from the site plugin config for CAPDMDWB
    $html .= get_config('capdmdwb', 'customcss');
    $html .= '</style>';
    $html .= html_writer::start_tag('div', array('id'=>'dwb_output'));
    // Loop round the dataset
    foreach($dwb as $d){

        $html .= '<table class="dwbitem" cellpadding="5">';

        if($lastType != 'mrq' && $d->data_type != 'select'){
            if($d->wrapper_id != $last_wrapperid){
                $html .= '<tr>';
                    $html .= '<td colspan="2" class="topictitle">'.nl2br(trim($d->topic_title)).'</td>';
                $html .= '</tr>';
            }
            if($d->preamble != $last_preamble){
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent preamble">'.nl2br(trim($d->preamble)).'</td>';
                $html .= '</tr>';
            }
            if($d->qpart != $last_qpart && $d->data_type != 'select'){
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent qpart">'.nl2br(trim($d->qpart)).'</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';
        }

        if($lastType == 'mrq' && $d->data_type != 'mrq'){
            $html .= $mrqHead.$mrq.$mrqFoot;
        }

        switch($d->data_type){
            case 'mrq':
                $mrq .= '<tr>';
                $mrq .= '<td width="5%">&nbsp;</td>';
//                $mrq .= '<td width="95%" class="dwbcontent datavalue"><input type="checkbox" checked disabled name="'.$d->data_id.'" value="'.$d->data_value.'"/> '.$d->data_explanation.$d->preamble.'</td>';
                $mrq .= '<td width="95%" class="dwbcontent datavalue">'.$checkImg.$d->data_explanation.'</td>';
                $mrq .= '</tr>';
                break;
            case 'textarea':
                $html .= '<table cellpadding="5">';
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent datavalue">'.nl2br(trim($d->data_value)).'</td>';
                $html .= '</tr>';
                $html .= '</table>';
                $html .= '<br /><br />';
                break;
            case 'highlight':
                $html .= '<table cellpadding="5">';
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent datavalue">'.trim($d->data_value).'</td>';
                $html .= '</tr>';
                $html .= '</table>';
                $html .= '<br /><br />';
                break;
            case 'mansopt':
            case 'mcq':
                $radioImg = '<img src="pix/icons/radio_icon.png">';
                $html .= '<table cellpadding="5">';
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent datavalue">'.$radioImg.'&nbsp;'.$d->data_explanation.'</td>';
                $html .= '</tr>';
                $html .= '</table>';
                $html .= '<br /><br />';
                break;
            case 'select':
                $html .= '<table cellpadding="5">';
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent qpart">'.nl2br(trim($d->qpart)).'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent preamble">'.nl2br(trim($d->preamble)).'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent datavalue select">'.$selectImg.'&nbsp;'.$d->data_option.'</td>';
                $html .= '</tr>';
                $html .= '</table>';
                $html .= '<br /><br />';
                break;
            default:
                $html .= html_writer::tag('p', $d->data_value, array('class'=>'datavalue'));
                break;
        }
        $lastType = $d->data_type;
        $last_wrapperid = $d->wrapper_id;
        $last_preamble = $d->preamble;
        $last_qpart = $d->qpart;
    }

    // Indicate this is the end of the workbook
    $end = html_writer::tag('p', get_string('endofresponses', 'capdmdwb'));
    $html .= '<table cellpadding="5">';
    $html .= '<tr>';
    $html .= '<td class="endofworkbook">'.$end.'</td>';
    $html .= '</tr>';
    $html .= '</table>';

    // Close the dwb-output div
    $html .= html_writer::end_tag('div');




        // Close the dwb-output div
        echo($html);

    }