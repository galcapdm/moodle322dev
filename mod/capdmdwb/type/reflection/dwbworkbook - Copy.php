<?php
//============================================================+
// File name   : example_061.php
// Begin       : 2010-05-24
// Last Update : 2014-01-25
//
// Description : Example 061 for TCPDF class
//               XHTML + CSS
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: XHTML + CSS
 * @author Nicola Asuni
 * @since 2010-05-25
 */
global $CFG, $DB, $SITE;

$_PAGECOUNTERFOOOTER = 0;
$_PAGECOUNTERHEADER = 0;
$_PRINTDATE = date("d M Y @ H:i");

// Include the main TCPDF library (search for installation path).
require_once($CFG->libdir.'/tcpdf/tcpdf.php');


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {

        global $_PAGECOUNTERHEADER, $course, $SITE;

        if($_PAGECOUNTERHEADER > 0){

            if ($this->header_xobjid === false) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$imgtype = TCPDF_IMAGES::getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
				if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
					$this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				}
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->y;
			}
			$cell_height = $this->getCellHeight($headerfont[2] / $this->k);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
			} else {
				$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
			$this->SetTextColorArray($this->header_text_color);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
			$this->SetX($header_x);
			$this->Cell($cw, $cell_height, $course->fullname, 0, 1, '', 0, '', 0);

                        $this->SetFont($headerfont[0], 'I', $headerfont[2] + 1);
			$this->SetX($header_x);
			$this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);

                        // header string
//			$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
//			$this->SetX($header_x);
//			$this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);

                        // print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));

                        if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = false;
		}

        } else {
            $_PAGECOUNTERHEADER = $_PAGECOUNTERHEADER + 1;
        }

    }

    public function Footer() {

        global $_PAGECOUNTERFOOTER, $_PRINTDATE;

        if($_PAGECOUNTERFOOTER > 0){
            $printdate = $_PRINTDATE;
            $cur_y = $this->y;
            $this->SetTextColorArray($this->footer_text_color);
            //set style for cell border
            $line_width = (0.85 / $this->k);
            $this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
            //print document barcode
            $barcode = $this->getBarcode();
            if (!empty($barcode)) {
                    $this->Ln($line_width);
                    $barcode_width = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
                    $style = array(
                            'position' => $this->rtl?'R':'L',
                            'align' => $this->rtl?'R':'L',
                            'stretch' => false,
                            'fitwidth' => true,
                            'cellfitalign' => '',
                            'border' => false,
                            'padding' => 0,
                            'fgcolor' => array(0,0,0),
                            'bgcolor' => false,
                            'text' => false
                    );
                    $this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width, '', (($this->footer_margin / 3) - $line_width), 0.3, $style, '');
            }
            $w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
            if (empty($this->pagegroups)) {
                    $pagenumtxt = get_string('printed', 'capdmdwb', array('printdate'=>$printdate)).get_string('page', 'capdmdwb').$w_page.$this->getAliasNumPage().get_string('pageof', 'capdmdwb').$this->getAliasNbPages();
            } else {
                    $pagenumtxt = get_string('printed', 'capdmdwb', array('printdate'=>$printdate)).get_string('page', 'capdmdwb').$w_page.$this->getPageNumGroupAlias().get_string('pageof', 'capdmdwb').$this->getPageGroupAlias();
            }
            $this->SetY($cur_y);
            //Print page number
            if ($this->getRTL()) {
                    $this->SetX($this->original_rMargin);
                    $this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
            } else {
                    $this->SetX($this->original_lMargin);
                    $this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
            }
        } else {
            $_PAGECOUNTERFOOTER = $_PAGECOUNTERFOOTER + 1;
        }
    }
}

    // create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($SITE->fullname);
    $pdf->SetTitle($SITE->fullname.' - '.get_string('pdf_header_title', 'capdmdwb', array('dwbname'=>$nm)));
    //$pdf->SetSubject('Digital Workbook subject');

    // set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, get_string('pdf_header_title', 'capdmdwb', array('dwbname'=>$nm)), get_string('pdf_header_string', 'capdmdwb'));

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaksfone
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

// Check to see if the checksum is correct else show an error page instead
if($_GET['checksum'] == md5($_GET['dwb'].'this is unguessible')){
    $userid = $_GET['dwb'];

    // Query is the same as in capdmdwb and capdmcert with the addition of the first two fields wrap.title and wrap.user_level being added to the list of fields returned
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

    $dwb = $DB->get_records_sql($strSQL, array('courseid1'=>$course->id, 'userid'=> $userid, 'courseid2'=>$course->id, 'dwbrole'=>'reflection'));


    // ---------------------------------------------------------

    // ######################################
    // Front page - START
    // ######################################

    $pdf->AddPage();

    $html = '<style type="text/css">';
    // Not all themes have customcss but in Moodle 3 but shoudl have the clean theme
    // Put your custom CSS in there
    //$html .= get_config('theme_clean'.$CFG->theme, 'customcss');
    $html .= get_config('capdmdwb', 'customcss');
    $html .= '</style>';

    // get
    $headerimg = get_config('capdmdwb', 'headerimg');
    $frontpageimg = get_config('capdmdwb', 'frontpageimg');
    $frontpageprint = get_config('capdmdwb', 'frontpageprintdate');
    $footerprint = get_config('capdmdwb', 'footerprintdate');
    $frontpageinfobox = get_config('capdmdwb', 'frontpageinfobox');

    if($headerimg != ''){
        $pdf->Image($CFG->dataroot.'/pix/mod/capdmdwb/'.$headerimg, $x=0, $y=0, $align='M');
    }
    if($frontpageimg != ''){
        $pdf->Image($CFG->dataroot.'/pix/mod/capdmdwb/'.$frontpageimg, $x=0, $y=85, $align='M');
    }

    if($frontpageinfobox){
        $html = '<style type="text/css">';
        // Not all themes have customcss but in Moodle 3 but shoudl have the clean theme
        // Put your custom CSS in there
        //$html .= get_config('theme_clean'.$CFG->theme, 'customcss');
        $html .= get_config('capdmdwb', 'customcss');
        $html .= '</style>';
        $html .= '<table cellpadding="10">';
        $html .= '<tr>';
        $html .= '<td class="dwb_output_frontpage_infobox">';
        $html .= html_writer::tag('h1', $course->fullname, array('class'=>'frontpagetitle'));
        $html .= html_writer::tag('h1', get_string('dwbfrontpagetitle', 'capdmdwb', array('dwbname'=>$nm)), array('class'=>'frontpagetitle'));
        if($frontpageprint){
            $html .= html_writer::tag('h2', get_string('printed', 'capdmdwb', array('printdate'=>$_PRINTDATE)), array('class'=>'frontpagetitle'));
        }
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $pdf->writeHTMLCell(170, 0, 20, 230, $html, 0, 0, false, true, '', true);
    } else {
        $html .= html_writer::start_tag('div', array('id'=>'dwb_output_frontpage'));
        $html .= html_writer::tag('h1', $course->fullname, array('class'=>'frontpagetitle'));
        $html .= html_writer::tag('h1', get_string('dwbfrontpagetitle', 'capdmdwb', array('dwbname'=>$nm)), array('class'=>'frontpagetitle'));
        if($frontpageprint){
            $html .= html_writer::tag('h2', get_string('printed', 'capdmdwb', array('printdate'=>$_PRINTDATE)), array('class'=>'frontpagetitle'));
        }
        $html .= html_writer::end_tag('div');
        $pdf->writeHTMLCell(170, 0, 20, 120, $html, 0, 0, false, true, '', true);
    }


    //$pdf->writeHTML($html, true, false, true, false, '');

    // ######################################
    // Front page - END
    // ######################################


    // ######################################
    // Follow on pages - START
    // ######################################

    $pdf->SetCellPaddings(0, 0, 0, 0);

    // Forces a "page break"
    $pdf->AddPage();

    // Track the topic number so we know when to display a topic title or not
    $last_topicno = -9999999;
    // MRQ types are difficult to handle as no idea if the next record is part of the mrq
    // need to track data_type and then act accordingly to add top and tail on the html
    // when an MRQ starts and stops!
    $lastType = '';
    $mrq = '';
    $mrqHead = '<table cellpadding="5">';
    $mrqFoot .= '</table><br /><br />';

    $html = '<style type="text/css">';
    // Pick up custom CSS from the site plugin config for CAPDMDWB
    $html .= get_config('capdmdwb', 'customcss');
    $html .= '</style>';
    $html .= html_writer::start_tag('div', array('id'=>'dwb_output'));
    // Loop round the dataset
    foreach($dwb as $d){

        $html .= '<table class="dwbitem" cellpadding="5">';

        if($lastType != 'mrq'){
            if($d->wrapper_id != $last_wrapperid){
                $html .= '<tr>';
                    $html .= '<td colspan="2" class="topictitle">'.nl2br(trim($d->topic_title)).'</td>';
                $html .= '</tr>';
            }
            //if($d->preamble != ''){
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent preamble">'.nl2br(trim($d->preamble)).'</td>';
                $html .= '</tr>';
            //}
            //if($d->qpart != ''){
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent qpart">'.nl2br(trim($d->qpart)).'</td>';
                $html .= '</tr>';
            //}
            $html .= '</table>';
        }

        $last_wrapperid = $d->wrapper_id;

        if($lastType == 'mrq' && $d->data_type != 'mrq'){
            $html .= $mrqHead.$mrq.$mrqFoot;
        }

        switch($d->data_type){
            case 'mrq':
                $mrq .= '<tr>';
                $mrq .= '<td width="5%">&nbsp;</td>';
                $mrq .= '<td width="95%" class="dwbcontent datavalue"><input type="checkbox" checked disabled name="'.$d->data_id.'" value="'.$d->data_value.'"/> '.$d->data_explanation.'</td>';
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
            case 'mansopt':
            case 'mcq':
                //$mansopt = html_writer::tag('span', array('class'=>'dwb-radio-icon'));
                if($d->data_value == -999){
                    $mansopt = '<input type="radio" disabled name="'.$d->data_id.'" value="'.$d->data_value.'"/> '.$d->data_explanation;
                } else {
                    $mansopt = '<input type="radio" disabled name="'.$d->data_id.'" value="'.$d->data_value.'" checked="checked"/> '.$d->data_explanation;
                }
                $html .= '<table cellpadding="5">';
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent datavalue">'.$mansopt.'</td>';
                $html .= '</tr>';
                $html .= '</table>';
                $html .= '<br /><br />';
                break;
            case 'select':
                //$select = '<select><option value="'.$d->data_value.'">'.$d->data_explanation.'</option></select>';
                $select = '<input type="radio" disabled name="'.$d->data_id.'" value="'.$d->data_value.'"/> '.$d->data_explanation;
                $html .= '<table cellpadding="5">';
                $html .= '<tr>';
                $html .= '<td width="5%">&nbsp;</td>';
                $html .= '<td width="95%" class="dwbcontent datavalue">'.$select.'</td>';
                $html .= '</tr>';
                $html .= '</table>';
                $html .= '<br /><br />';
                break;
            default:
                $html .= html_writer::tag('p', $d->data_value, array('class'=>'datavalue'));
                break;
        }
        $lastType = $d->data_type;
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

    $pdf->writeHTML($html, true, false, true, false, '');

    // ######################################
    // Follow on pages - END
    // ######################################
} else {
    // ######################################
    // Error page - START
    // ######################################

    $errorimage = get_config('capdmdwb', 'errorimg');

    $pdf->AddPage();

    $html = '<style type="text/css">';
    // Not all themes have customcss but in Moodle 3 but shoudl have the clean theme
    // Put your custom CSS in there
    //$html .= get_config('theme_clean'.$CFG->theme, 'customcss');
    $html .= get_config('capdmdwb', 'customcss');
    $html .= '</style>';
    $html .= html_writer::tag('h1', get_string('errorheader', 'capdmdwb'));
    $html .= html_writer::tag('p', get_string('errordesc', 'capdmdwb'));

    if($errorimage != ''){
        $pdf->Image($CFG->dataroot.'/pix/mod/capdmdwb/'.$errorimage, $x=20, $y=85, $align='M');
    } else {
        $pdf->Image('pix/dwb_error.gif', $x=20, $y=85, $align='M');
    }

    $pdf->writeHTML($html, true, false, true, false, '');

    // ######################################
    // Error page - END
    // ######################################
}

// reset pointer to the last page
$pdf->lastPage();

//Close and output PDF document
$pdf->Output('dwb-'.$nm.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+