define(['jquery', 'mod_capdmdwb/datatables-1.10.13'], function($, dt, notification) {

    "use strict";

    // Key Filter support
    function status_filter_off() 
    // -------------------------
    {
//	console.log("STATUS FILTER OFF");	    
	var table = $("#capdmdwb_tot_all").DataTable(); 
	// Remove all filters
	table.search("").columns().search("").draw();
	
	// and indicators
	document.getElementById("dwb-key-very").style.backgroundColor = "initial";
	document.getElementById("dwb-key-some").style.backgroundColor = "initial";
	document.getElementById("dwb-key-notc").style.backgroundColor = "initial";    
//	console.log("STATUS FILTER OFF - EXIT");	    	    
    };
    
    function status_filter_on(c, e) 
    // ----------------------------
    {
//	console.log("STATUS FILTER ON");	    	    
	var table = $("#capdmdwb_tot_all").DataTable(); 
	
	    // Add filter to the specified element.  The hidden text string in the element has the same value as the ID
	table.columns(c).search(e).draw();
	
	// Add Indicator
	document.getElementById("dwb-key-very").style.backgroundColor = (e == "dwb-key-very") ? "#dddddd" : "initial";
	document.getElementById("dwb-key-some").style.backgroundColor = (e == "dwb-key-some") ? "#dddddd" : "initial";
	document.getElementById("dwb-key-notc").style.backgroundColor = (e == "dwb-key-notc") ? "#dddddd" : "initial";
//	console.log("STATUS FILTER ON - EXIT");	    	    
    };
    
    $(document).ready(function() 
    // ------------------------
    {
	// DataTables attached to these table IDs
	// Planner
        $('#capdmdwb_tot_all').DataTable();	$('#capdmdwb_tot_one').DataTable();	$('#capdmdwb_tot_student').DataTable();
	// Reflection
	$('#capdmdwb_student_dwbs').DataTable();	
	// Journals
	$('#capdmdwb_student_journals').DataTable();	
    });    
    
    $(document).on('click', '.dwb-key-toggle', function(e)
    // ---------------------------------------------------
    {
        e.preventDefault();
        var p = $(e.currentTarget);
        var c = $(p).attr('col');  var s = $(p).attr('search');

	if (typeof(s) !== 'undefined' && typeof(c) !== 'undefined') status_filter_on(c, s);
	else status_filter_off();
    });
    
    return {
        init: function() {
            console.log('Init of Datables');
	}
    }    
});
