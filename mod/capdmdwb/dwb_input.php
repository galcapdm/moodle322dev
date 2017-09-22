<?php // $Id: edit.php,v 1.167.2.11 2008/07/10 08:05:52 moodler Exp $

    require_once('../../config.php');
    require_once($CFG->libdir.'/dmllib.php');

/*
 *  This handler will either INSERT or UPDATE an input record, depending
 *  on whether the record EXISTS or not,
 *  or it will return the value of the requested record, which may be null.
 *  The only parameter in the query string is a JSON array which will have:
 *  0) 'record' or 'retrieve' depending on intent
 *  1) the Moodle course ID (integer)
 *  2) a Module ID (production Code)
 *  3) an INPUT element id, which may be the choice string for an mcq, mrq or select
 *  4) is the value to 'record', or null if a 'retrieve'
 *  5) the type, e.g. 'mcq'
 *  6) any explanation, e.g. for an mcq, mrq or select
 *  7) any option, e.g. the ordinal value of the MCQ/MRQ selection
 *  Note that the user id is picked up from the VLE.
 */

$dwb_input_table = $CFG->prefix."capdmdwb_response";  // Table name

$_record      = "record";   // The First JSON parameter is the DB operation
$_retrieve    = "retrieve";
$_retrieveall = "retrieveall";
$_delete      = "delete";

// Use the user id unless told otherwise.
$user_id = optional_param('id', $USER->id, PARAM_INT);    

$retJSON = array();  // Null array

// Pick up the JSON parameter
$param = json_decode(stripslashes(mb_convert_encoding($_REQUEST['value'], 'UTF-8','HTML-ENTITIES')));  // Returns an Object


// GALDWB
// If UQ Student, them fix Param 4.  Look for Student, as in uqstudent
//if (strpos(strtolower($USER->username), 'student', 0) > -1){
//  $param[4] = "This DWB feature is disabled for guest access!";
//}
// GALDWB

// What is the instruction field?
if (strcmp($param[0],$_record) == 0) {  // RECORD
  
  // if the record EXISTS then we have to UPDATE
  if (record_exists_sql("SELECT user_id,mod_id FROM ".$dwb_input_table." WHERE user_id='".$user_id."' AND mod_id='".$param[2]."' AND data_id='".$param[3]."'")) {

    // UPDATE the value field instead
    $retJSON = execute_sql("UPDATE ".$dwb_input_table." SET data_value = '".$param[4]."',data_explanation='".$param[6]."',data_option='".$param[7]."' WHERE user_id='".$user_id."' AND mod_id='".$param[2]."' AND data_id='".$param[3]."'");
  }
  else {  // INSERT a record as it EXISTS

    // Now run the database query to insert the data
      $retJSON = execute_sql("INSERT INTO ".$dwb_input_table." (user_id,mod_id,data_id,data_value,data_type,data_explanation,data_option) VALUES ('".$user_id."','".$param[2]."','".$param[3]."','".$param[4]."','".$param[5]."','".$param[6]."','".$param[7]."')");
//add_to_log(104, 'debug', 'get', 'INSERT', "INSERT INTO ".$dwb_input_table." (user_id,mod_id,data_id,data_value,data_type,data_explanation,data_option) VALUES ('".$user_id."','".$param[2]."','".$param[3]."','".$param[4]."','".$param[5]."','".$param[6]."','".$param[7]."')");
    }
 }
 else if (strcmp($param[0],$_retrieve) == 0) {
    $retJSON = get_record_sql("SELECT user_id,mod_id,data_id,data_value,data_type,data_option,data_explanation FROM ".$dwb_input_table." WHERE user_id='".$user_id."' AND mod_id='".$param[2]."' AND data_id='".$param[3]."'");

    // The SELECT is returned into the $retJSON Associative ARRAY
 }
 else if (strcmp($param[0],$_retrieveall) == 0) {
    $rs = get_recordset_sql("SELECT user_id,mod_id,data_id,data_value,data_type,data_option,data_explanation FROM ".$dwb_input_table." WHERE user_id='".$user_id."' AND mod_id='".$param[2]."' AND data_id LIKE '".$param[3]."%'");

    // The SELECT potentially returns a multi-dim array
    if (rs_EOF($rs)) {
        $retJSON = array();
    }
    else {
         while (!rs_EOF($rs)) {
	     array_push($retJSON, rs_fetch_record($rs));  // Is this right?
	     rs_next_record($rs);
	 }
    }
    rs_close($rs);
 }
 else if (strcmp($param[0],$_delete) == 0) { // DELETE some entries
   $retJSON = execute_sql("DELETE FROM ".$dwb_input_table." WHERE user_id='".$user_id."' AND mod_id='".$param[2]."' AND data_id LIKE '".$param[4]."%';");

//add_to_log($course->id, "DELETE", "DEBUG", "DELETE FROM ".$dwb_input_table." WHERE user_id='".$user_id."' AND mod_id='".$param[2]."' AND data_id LIKE '".$param[4]."%';", "777");
 }
 else {
   // Not right.  Has to be one of the three above
   $retJSON = array();
 }


// Set up some headers for the reply
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
header("Cache-Control: no-cache, must-revalidate" ); 
header("Pragma: no-cache" );
header("Content-Type: text/xml; charset=utf-8");

/*
The most important header is the "Expires" header. Set it to a date that has already passed as IE tends to cache the response regardless of the other headers.

Content-Type?  We're sending plain text (JSON)
*/


echo json_encode($retJSON);  // 

// That's all

/*
 * HISTORY
 *
 * 12-Apr-2012: KWC updated for Moodle2 and the additional field (Moodle course number)
 *
 * 11-Nov-2009: KWC Changed the table name from cis_ajax to capdm_dwb_input
 *              to reflect the more general use.
 * 23-Oct-2010: KWC changed to accommodate 6 parameters in a single call, not over three calls.
 */
?>
