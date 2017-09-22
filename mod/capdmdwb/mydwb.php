<?php

require_once("../../config.php");
require_once("lib.php");
//require_once($CFG->libdir.'/pdflib.php');
require_once($CFG->libdir.'/tcpdf/tcpdf.php');

// Generate a customised Digital Workbook
// CAPDM: KWC 16-Nov-2009



// Extend the TCPDF class to create custom Header and Footer
class myDWB extends TCPDF{
    //Page header
    public function Header() {
        // Logo
        $this->Image('http://192.168.254.237/moodle/theme/opus/images/'.'wb_hl_blue.jpg', 10, 8, 15);
        // Set font
        $this->SetFont('FreeSerif', 'B', 20);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30, 10, 'Title', 0, 0, 'C');
        // Line break
        $this->Ln(20);
    }
    
    // Page footer
    public function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('FreeSerif', 'I', 8);
        // Page number
        //$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');  *** Breaks the page
    }
}  // ============== End of Class ====================


    $id    = optional_param('id', 0, PARAM_INT); // Course Module ID, or

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
        if (! $capdmdwb = get_record("capdmdwb", "id", $cm->instance)) {
            error("Course module is incorrect: ".$cm->instance);
        }
    }

// Now generate the PDF Object

$doc = new myDWB(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

layout_page($cm, $course, $capdmdwb, $doc);  // web page

// ============ Support functions for text/html ===============


function layout_page($cm, $course, $capdmdwb, $doc) {
  global $CFG, $USER;

  require_login($course->id);

  // set document information
  $doc->SetCreator(PDF_CREATOR);
  $doc->SetAuthor('A Student');
  $doc->SetTitle('My DWB for Course XXX');
  $doc->SetSubject('Course XXX');
  $doc->SetKeywords('TCPDF, PDF, example, test, guide');
  
  //$doc->print_header = true;
  //$doc->print_footer = true;
  
  //set margins
  $doc->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
  $doc->SetHeaderMargin(PDF_MARGIN_HEADER);
  $doc->SetFooterMargin(PDF_MARGIN_FOOTER);
  
  //set auto page breaks
  $doc->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM); 
  
  
  $doc->AddPage();
  $doc->Write(5, 'Digital Workbook for '.$USER->firstname.' '.$USER->lastname);
  $doc->Ln();
  $doc->Ln();
  

  // Now the detail proper
  $rs = get_recordset_sql("SELECT w.*,a.*,i.* FROM capdm_dwb_wrapper w INNER JOIN capdm_dwb_activity a ON w.wrapper_id = a.wrapper_id INNER JOIN capdm_dwb_input i ON a.activity_id = i.data_id WHERE w.myile_prod_code='ptds' AND i.user_id=2");
  
  // Limit to this course and this user
  if (rs_EOF($rs)) {
    $doc->Write(7, "Your Digital Workbook is empty.");
  }
  else {
    $topic_no = 1;  // Can I guarantee that they start at 1?
    $session_no = -99;  // Something illegal
    $cwidths = array(40, 120);
    
    //-- tab control start 
    $doc->AddPage();
    // Open up tab 1
    $doc->Write(7, "TopicX ".$topic_no.": ".$r->topic_title."XX");  
    $doc->Ln();  $doc->Ln();
    
    while (!rs_EOF($rs)) {
      $r = rs_fetch_record($rs);
      
      if ($r->topic_no != $topic_no) {
	if ($session_no != -99) ; //$doc->Cell(array_sum($cwidths), 0, '', 'T');
	
	$topic_no = $r->topic_no;
	$session_no = -99;  // Reset this value
	$doc->AddPage(); 
	$doc->Ln();
	$doc->Write(7, "TopicY ".$topic_no.": ".$r->topic_title."YY");  
	$doc->Ln();  $doc->Ln();
      }
      
      
      if ($session_no != $r->session_no) {
	if ($session_no != -99) $doc->Ln();
	
	// Now open a next table
        // Colors, line width and bold font
        $doc->SetFillColor(255, 0, 0);
        $doc->SetTextColor(255);
        $doc->SetDrawColor(128, 0, 0);
        $doc->SetLineWidth(0.3);
        $doc->SetFont('', 'B');


        // Header
        $doc->MultiCell(array_sum($cwidths), 5, "Session ".$r->session_no.": ".$r->session_title, 0, 'L', 1, 1, '', '', true);

        $doc->MultiCell($cwidths[0], 5, "Activity: ", 0, 'R', 1, 0, '', '', true);
        $doc->MultiCell($cwidths[1], 5, $r->topic_title, 1, 'L', 1, 1, '', '', true);

        // Color and font restoration
        $doc->SetFillColor(224, 235, 255);
        $doc->SetTextColor(0);
        $doc->SetFont('');

	$session_no = $r->session_no;
      }

      // Now the real data
            
      $doc->MultiCell($cwidths[0], 5, "Title: ", 0, 'R', 0, 0, '', '', true);
      $doc->MultiCell($cwidths[1], 5, $r->title, 1, 'L', 1, 1, '', '', true);

      if (!is_null($r->preamble) && strlen($r->preamble) != 0) {
	$doc->MultiCell($cwidths[0], 5, "Preamble: ", 0, 'R', 0, 0, '', '', true);
	$doc->MultiCell($cwidths[1], 5, $r->preamble, 1, 'L', 1, 1, '', '', true);
      }
      
      $doc->MultiCell($cwidths[0], 5, "Question: ".$r->activity_id, 0, 'R', 0, 0, '', '', true);
      $doc->MultiCell($cwidths[1], 5, $r->qpart, 1, 'L', 1, 1, '', '', true);

      if (!is_null($r->ansopt) && strlen($r->ansopt) != 0) {
	$doc->MultiCell($cwidths[0], 5, "Hint: ", 0, 'R', 0, 0, '', '', true);
	$doc->MultiCell($cwidths[1], 5, $r->ansopt, 1, 'L', 1, 1, '', '', true);
      }

      // Finally ... my amswer
      $doc->MultiCell($cwidths[0], 5, "My Answer: ", 0, 'R', 0, 0, '', '', true);
      $doc->MultiCell($cwidths[1], 5, $r->data_value, 1, 'L', 1, 1, '', '', true);
      //$doc->Ln();
      
      rs_next_record($rs);
    }
    
    //$doc->MultiCell(array_sum($cwidths), 5, '', 0, 'T', 0, 1, '', '', true);
    $doc->Ln();
  }
  
  rs_close($rs);
  
  // Finally, output the document
  $doc->Output();

} // End of layout_page

?>
