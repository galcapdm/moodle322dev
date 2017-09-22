YUI.add('moodle-mod_capdmdwb-dwb', function(Y) {
    
    this.Y = Y;
    
    var DWBNAME = 'capdmdwb_dwb';
    var DWB = function() {
        DWB.superclass.constructor.apply(this, arguments);
    }
    
    Y.extend(DWB, Y.Base, {
	event : null,
        initializer : function(config) { //'config' contains the parameter values
	    
            YUI().use('base', 'node', 'node-event-simulate', 'selector-css3', 'tabview', function (Y) {
		
		
					var tabview = new Y.TabView({srcNode: '#dwb-details'});
					tabview.render(); //tabview.selectChild(0); // Select the opening tab by default
                
		/*
                 *  When the DOM object has been built and is ready for manipulation - fire these functions
                 *  to populate the form and reveal a clients answers/details 
                 * 
                 */
		Y.on("domready", function () {
		    onDWBGroupLoad();  onDWBTabLoad();  onPlannerDWBTabLoad();
		    
		    var studentlist = document.getElementById('toggle_studentlist');
		    if (typeof(studentlist) != 'undefined' && studentlist != null) {
			var mystudentlist= Y.one('#toggle_studentlist');
	           	mystudentlist.on("click", function(e) {
			    var node = Y.one('#studentlist');
			    node.toggleClass('display-toggle-detail-show');
			});
		    }
		    
		    // Check to see if the noworkbook id is present on the page
		    // If not render the tab control as there is a workbook present
		    var noworkbook = Y.one('#noworkbook');
		    if (!noworkbook) {
			var tabview = new Y.TabView({srcNode:'#dwb-details'});
			tabview.render();
		    }
		    
		    var backToSummary = Y.all('.dwb-goto-summary');
		    backToSummary.on("click", function(e) {
			tabview.selectChild(0);
		    });
		    
		    var button = Y.all('.dwb-goto-tab');
		    button.on("click", function(e){
			// first, remove all the instances of the qpart-highlight class
			Y.all(".dwb-response-qpart-highlight").removeClass("dwb-response-qpart-highlight");
			
			// get the id of the clicked on activity
			var act_id = e.currentTarget.getAttribute("act_id");
			// use the above id to build the node object
			var node = Y.one("#dwb-detail_" + act_id);

			// change to the tab indicated by the tabid value
			tabview.selectChild(e.currentTarget.getAttribute('tabid'));
			// add the qpart-highlight class to the selected activity node
			node.addClass("dwb-response-qpart-highlight");
			// finally, scroll the selected activity into view
			node.scrollIntoView();
		    });		
		});
		
		var onDWBTabLoad = function(){
                    Y.all('.dwb-tabs').each( function(e) {
			//		       alert("dwb-tabs");
                    }); 
                };
                
                
		var onDWBGroupLoad = function(){
                    Y.all('.dwb-groups').each( function(e) {  
			//		       alert("dwb-groups");
                    });                  
		}; 

		var onPlannerDWBTabLoad = function(){
                    Y.all('.dwb-planner-tab-selected').each( function(e) {
			tabview.selectChild(Y.one(e).getAttribute('dwb-tab-index'));
//			alert("GOT "+ Y.one(e).getAttribute('dwb-tab-index'));			
                    }); 
                };
                
            });   
        }
    }, {
        NAME : DWBNAME, //module name is something mandatory. 
        //It should be in lower case without space 
        //as YUI use it for name space sometimes.
        ATTRS : {
            aparam : {}
        } // Attributs are the parameters sent when the $PAGE->requires->yui_module calls the module. 
        // Here you can declare default values or run functions on the parameter. 
        // The param names must be the same as the ones declared 
        // in the $PAGE->requires->yui_module call.
    });
    M.mod_capdmdwb = M.mod_capdmdwb || {}; //this line use existing name path if it exists, ortherwise create a new one. 
    //This is to avoid to overwrite previously loaded module with same name.
    M.mod_capdmdwb.init_dwb = function(config) { //'config' contains the parameter values
        return new DWB(config); //'config' contains the parameter values
    }
}, '1.0', {
    requires:['base', 'node', 'node-event-simulate', 'selector-css3', 'tabview']
});

