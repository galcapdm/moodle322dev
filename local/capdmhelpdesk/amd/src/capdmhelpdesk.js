define(['core/ajax', 'core/templates', 'core/notification', 'core/str'], function(ajax, templates, notification, str) {

    "use strict";

    var hidepos = 0;
    var userID = $('#userid').val();
    var userType = $('#usertype').val();
    var msgID;
    var toggleStatus = 0;
    var objHeights = {};
    var collapseID;
    var objQS = {};
    var searchBool = 1;
    var searchY = 50;
    var lastTime = 0;

    $(document).ready(function()
    // ------------------------
    {
        // Validate new message form - START.
        $('#form_new_message').each(function () {
            $(this).validate({
                rules: {
                    newsubject: "required",
                    newmessage: "required",
                    newcategory: "required",
                },
                messages: {
                    newsubject: "Please enter a subject line",
                    newmessage: "Please enter a description of the help you are needing",
                    newcategory: "Please enter a value for the category",
                },
                errorElement: "em",
                errorPlacement: function ( error, element ) {
                    // Add the `help-block` class to the error element
                    error.addClass( "help-block" );

                    // Add `has-feedback` class to the parent div.form-group
                    // in order to add icons to inputs
                    element.parents( ".col-sm-5" ).addClass( "has-feedback" );

                    if ( element.prop( "type" ) === "checkbox" ) {
                            error.insertAfter( element.parent( "label" ) );
                    } else {
                            error.insertAfter( element );
                    }

                    // Add the span element, if doesn't exists, and apply the icon classes to it.
                    if ( !element.next( "span" )[ 0 ] ) {
                        $( "<span class='icon fa fa-times form-control-feedback'></span>" ).insertAfter( element );
                    }
                },
                success: function ( label, element ) {
                    // Add the span element, if doesn't exists, and apply the icon classes to it.
                    if ( !$( element ).next( "span" )[ 0 ] ) {
                        $( "<span class='icon fa fa-check form-control-feedback'></span>" ).insertAfter( $( element ) );
                    }
                },
                highlight: function ( element, errorClass, validClass ) {
                    $( element ).parents( ".col-sm-5" ).addClass( "has-error" ).removeClass( "has-success" );
                    $( element ).next( "span" ).addClass( "fa-times" ).removeClass( "fa-check" );
                },
                unhighlight: function ( element, errorClass, validClass ) {
                    $( element ).parents( ".col-sm-5" ).addClass( "has-success" ).removeClass( "has-error" );
                    $( element ).next( "span" ).addClass( "fa-check" ).removeClass( "fa-times" );
                },
                submitHandler: function ( form ) {
                    //var data = $( form ).serialize();

                    $( '#new_message_submit' ).attr('disabled', true);

                    // Hide the form and show an information message as sending emails can cause a delay
                    toggleNewForm(0, 'new');

                    var newMsg = $('#newmessage').val();
                    var newSub = $('#newsubject').val();
                    var catVal = $('#newcategory').val();

                    var promises = ajax.call([
                        { methodname: 'local_capdmhelpdesk_save_message', args:{ userid: userID, category: catVal, subject: newSub, message: newMsg, updateby: userID, status: 0, readflag: 1, params: ''} }
                    ]);
                    promises[0].done(function(data) {
                        // We have the data - lets re-render the template with it.
                        templates.render('local_capdmhelpdesk/message_list', data).done(function(html, js) {
                            $('[data-region="capdmhelpdesk-msg-list"]').replaceWith(html);
                            // And execute any JS that was in the template.
                            templates.runTemplateJS(js);

                            $( '#msgid_'+data.newmsgid+' div.status').toggleClass('latestmsg');

                            // Show the form and hide the information message now the message has been sent.
                            // Else the wating message remains and the form is not available if the student wants to send another message.
                            toggleNewForm(1, 'new');

                            $( '#capdmhelpdesk-new-msg-holder' ).animate({
                                left: hidepos
                            }, 750, function(){
                                // Show message list in full opacity.
                                $( '#capdmhelpdesk-msg-list').fadeTo(750, 1);
                                // Reset the values of the input/select boxes.
                                $('#newmessage').val('');
                                $('#newsubject').val('');
                                $('#newcategory').val('');
                                // Enable the submit button again
                                var validator = $( '#form_new_message' ).validate();
                                validator.resetForm();

                                $( '#new_message_submit' ).attr('disabled', false);

                                $( '#add_new_message' ).toggleClass( 'showingnew' );
                                $( '#add_new_message' ).toggleClass( 'fa-rotate-45' );

                                $.when(str.get_string('newmessageadded', 'local_capdmhelpdesk')).done(function(localizedEditString) {
                                    capdmhelpdesk_alert_msg(localizedEditString, 0);
                                });
                            });

                        }).fail(notification.exception);
                    }).fail(notification.exception);

                    // Always return false so the form does not submit as we want to use Ajax.
                    return false;
                }
            });
        });
        // Validate new message form - END.

        // Validate reply forms - START.
        // Do this as a functions as it needs to be called on ajax reload
        // else the forms are not recognised in the DOM.
        capdmhelpdesk_set_reply_validation();
        // Validate reply forom - END.
    });

    // Listeners.

    // Add new message button listener.
    // -------------------------------------------------------------------------
    $( '#add_new_message' ).on('click', function(e){
        var p = $( '#capdmhelpdesk-holder' );
        var pos = p.offset();
        var nmx = $( '#capdmhelpdesk-new-msg-holder' ).width();
        hidepos = ((pos.left + nmx)*-1) - 50;

        // Find the top position of the message list and align
        // the new message box to that.
        var p2 = $( "#capdmhelpdesk-msg-list" );
        var pos2 = p2.position();
        var posY = pos2.top;
        $( '#capdmhelpdesk-new-msg-holder' ).css('top', posY);

        // Hide any expanded replies sections
        $('.messageholder.replies').hide(750);

        if(! $( this ).hasClass('showingnew') ){
            $( '#capdmhelpdesk-msg-list').fadeTo(500, 0.25, function(){
                $( '#capdmhelpdesk-new-msg-holder' ).animate({
                    left: 0
                }, 750);
                $( '#newsubject' ).focus();
            });
        } else {
            $( '#capdmhelpdesk-new-msg-holder' ).animate({
                left: hidepos
            }, 750, function(){
                // Show message list in full opacity.
                $( '#capdmhelpdesk-msg-list').fadeTo(750, 1);
            });
        }
        $( this ).toggleClass( 'showingnew' );
        $( this ).toggleClass( 'fa-rotate-45' );

        // Hide any validation icons or text that may be displayed.
        $( '.form-control-feedback' ).detach();
        $( '.error.help-block' ).detach();
    });

    /*
     *  Click handler to listen for a click on a message.
     *  This will use AJAX to call for the replies to this message ID
     *  and use a template to replace the contents.
     */
    $( 'body' ).on( 'click', '.messagedetails', function(e){
        msgID = $( this ).attr('id');
        var id = msgID.split('_')[1];
        var thisParent = $( '#msgid_'+id );
        var adminview = false;
        if($( this ).hasClass('adminview')){
            adminview = true;
        }

        if(! $(this).hasClass('opened') ){

            // Reset collapsed heights if needed!
            capdmhelpdesk_reset_messagedetails_heights();
            // Disable the toggle button and set toggle status to we can track the state.
            //$( '#toggle_categories' ).attr( 'disabled', true );
            $( '.disable_button' ).attr( 'disabled', true );
            toggleStatus = 1;

            $( this ).animate({
                marginLeft: '+=30'
            }, 300, function(){
                // Callback.
                thisParent.find('i').fadeIn(300);
            });

            // Hide all messageholder div's except this one
            $( '.messageholder-main').not( thisParent ).hide(500);

            // Show the replies section and the original message.
            $( '#msgid_'+id+'_replies').show(750);
            $( '#origmessage_'+id).show(750);
            $( this ).addClass('opened');

            // First - reload the data for the page.
            var promises = ajax.call([
                { methodname: 'local_capdmhelpdesk_get_replies', args:{ replyto: id } },
                //{ methodname: 'local_capdmhelpdesk_update_message', args:{ msgid: id, field: 'readflag', val: 1 } }
            ]);
            promises[0].done(function(data) {
                // We have the data - lets re-render the template with it.
                templates.render('local_capdmhelpdesk/message_replies', data).done(function(html, js) {
                    $('[data-region="msgid_'+id+'_replies"]').replaceWith(html);
                    // And execute any JS that was in the template.

                    // Show the reply button.
                    $( '#action_reply_'+id ).slideDown(250);

                    if( ! adminview ){
                        var promises = ajax.call([
                            { methodname: 'local_capdmhelpdesk_update_message', args:{ msgid: id, field: 'readflag', val: 1 } }
                        ]);

                        promises[0].done(function(data) {
                                $( '#msgid_'+id+'_details' ).find('p.unread').removeClass('unread');
                        }).fail(notification.exception);
                    }

                    templates.runTemplateJS(js);
                }).fail(notification.exception);
            }).fail(notification.exception);

        } else {
            thisParent.find('i').not('.keepshow').fadeOut(300);
            $( this ).animate({
                marginLeft: '-=30'
            }, 400, function(){
                // Callback.
            });
            // Hide the replies section, the reply form and the original message.
            $( '#action_reply_'+id ).slideUp(250);
            $( '#msgid_'+id+'_replies').hide(750);
            $( '#msgid_'+id+'_reply_holder').hide(300);
            $( '#msgid_'+id+'_reply_holder').removeClass('opened');
            $( '#origmessage_'+id).hide(750);
            $( this ).removeClass('opened');
            // Now show all the other messageholder div's.
            $( '.messageholder-main' ).show(500);
            // Enable the toggle button.
            //$( '#toggle_categories' ).attr('disabled', false);
            $( '.disable_button' ).attr( 'disabled', false );
            toggleStatus = 0;
        }

    });

    // Show only the selected types of message based on status.
    $( 'body' ).on( 'click', '.helpdesk-control-button', function(e){
        var btnid = $( this ).attr('id');
        var show = btnid.split( '_' )[1];

        // If a message is open then close it first
        var openMsg = $( '.messagedetails.opened' );
        if(openMsg.length > 0){
            var id = $( openMsg ).attr( 'id' ).split('_')[1];
            var thisParent = $( '#msgid_'+id );
            thisParent.find('i').not('.keepshow').fadeOut(300);
            $( openMsg ).animate({
                marginLeft: '-=30'
            }, 400, function(){
                // Callback.
            });
            // Hide the replies section, the reply form and the original message.
            $( '#action_reply_'+id ).slideUp(250);
            $( '#msgid_'+id+'_replies').hide(750);
            $( '#msgid_'+id+'_reply_holder').hide(300);
            $( '#msgid_'+id+'_reply_holder').removeClass('opened');
            $( '#origmessage_'+id).hide(750);
            $( this ).removeClass('opened');
            // Now show all the other messageholder div's.
            $( '.messageholder-main' ).show(500);
            // Enable the toggle button.
            $( '.disable_button' ).attr('disabled', false);
            toggleStatus = 0;
        }

        // Hide any action buttons if shown
        $( '.messageholder-replies' ).hide(300);
        // Close any opened messages.
        $( '.opened div.messagedetails' ).animate({
            marginLeft: '-=30'
        }, 300, function(){
            // Callback.
        });
        var openelement = $( '.opened' ).attr( 'id' );
        if( openelement ){
            $( '.opened' ).removeClass( 'opened' );
            $( '#closeicon_'+openelement.split( '_' )[1] ).fadeOut(200);
        }

        switch(show){
            case 'all':
                $( '.holder_status_0' ).show(500);
                $( '.holder_status_1' ).show(500);
                break;
            case 'open':
                $( '.holder_status_0' ).show(500);
                $( '.holder_status_1' ).hide(500);
                break;
            case 'closed':
                $( '.holder_status_0' ).hide(500);
                $( '.holder_status_1' ).show(500);
                break;
            case 'age':
                switch(btnid.split( '_' )[2]){
                    case '0':
                        $( '.holder_age_4' ).show(300);
                        $( '.holder_age_8' ).show(300);
                        $( '.holder_age_12' ).show(300);
                        $( '.holder_age_24' ).show(300);
                        $( '.holder_age_25' ).show(300);
                        break;
                    case '4':
                        $( '.holder_age_4' ).show(300);
                        $( '.holder_age_8' ).hide(300);
                        $( '.holder_age_12' ).hide(300);
                        $( '.holder_age_24' ).hide(300);
                        $( '.holder_age_25' ).hide(300);
                        break;
                    case '8':
                        $( '.holder_age_4' ).hide(300);
                        $( '.holder_age_8' ).show(300);
                        $( '.holder_age_12' ).hide(300);
                        $( '.holder_age_24' ).hide(300);
                        $( '.holder_age_25' ).hide(300);
                        break;
                    case '12':
                        $( '.holder_age_4' ).hide(300);
                        $( '.holder_age_8' ).hide(300);
                        $( '.holder_age_12' ).show(300);
                        $( '.holder_age_24' ).hide(300);
                        $( '.holder_age_25' ).hide(300);
                        break;
                    case '24':
                        $( '.holder_age_4' ).hide(300);
                        $( '.holder_age_8' ).hide(300);
                        $( '.holder_age_12' ).hide(300);
                        $( '.holder_age_24' ).show(300);
                        $( '.holder_age_25' ).hide(300);
                        break;
                    case '25':
                        $( '.holder_age_4' ).hide(300);
                        $( '.holder_age_8' ).hide(300);
                        $( '.holder_age_12' ).hide(300);
                        $( '.holder_age_24' ).hide(300);
                        $( '.holder_age_25' ).show(300);
                        break;
                }
                break;
        }

    });

    // Reload this user's messages.
    $( 'body' ).on( 'click', '#reload', function(){

        // Need to set some globals else things will get out of sync.
        toggleStatus = 0;

        $( '#capdmhelpdesk-msg-list').fadeTo(300, 0.25, function(){

            $.when(str.get_string('reloading', 'local_capdmhelpdesk')).done(function(localizedEditString) {
                capdmhelpdesk_alert_msg(localizedEditString, 0);
            });

            switch(userType){
                case 'admin':
                    // Clear any search results first.
                    capdmhelpdesk_clear_search();
                    var promises = ajax.call([
                        { methodname: 'local_capdmhelpdesk_reload_messages_admin', args:{ userid: userID } }
                    ]);
                    promises[0].done(function(data) {
                        // We have the data - lets re-render the template with it.
                        templates.render('local_capdmhelpdesk/message_list_admin', data).done(function(html, js) {
                            $('[data-region="capdmhelpdesk-msg-list"]').replaceWith(html);
                            // And execute any JS that was in the template.
                            templates.runTemplateJS(js);

                            capdmhelpdesk_set_reply_validation();

                            // Fade back to normal.
                            setTimeout(function(){
                                $( '#capdmhelpdesk-msg-list').fadeTo(300, 1, function(){
                                    capdmhelpdesk_alert_msg('', -1);
                                });
                            }, 750);
                        }).fail(notification.exception);
                    }).fail(notification.exception);
                    break;
                default:
                    var promises = ajax.call([
                        { methodname: 'local_capdmhelpdesk_reload_messages', args:{ userid: userID } }
                    ]);
                    promises[0].done(function(data) {
                        // We have the data - lets re-render the template with it.
                        templates.render('local_capdmhelpdesk/message_list', data).done(function(html, js) {
                            $('[data-region="capdmhelpdesk-msg-list"]').replaceWith(html);
                            // And execute any JS that was in the template.
                            templates.runTemplateJS(js);
                            // Fade back to normal.
                            setTimeout(function(){
                                $( '#capdmhelpdesk-msg-list').fadeTo(300, 1, function(){
                                    capdmhelpdesk_alert_msg('', -1);
                                });
                            }, 750);
                        }).fail(notification.exception);
                    }).fail(notification.exception);
                    break;
            }
        });
    });

    // Action buttons
    $( 'body' ).on('click', '.capdmhelpdesk-action-button', function(){
        var btnid = $( this ).attr('id');
        var action = btnid.split('_')[1];
        var id = btnid.split('_')[2];

        // Check what is being requested.
        switch(action){
            case 'reply':   // Show the reply form.
                if( $( '#msgid_'+id+'_reply_holder' ).hasClass('opened') ){
                    $( '#msgid_'+id+'_reply_holder' ).hide(250);
                    $( '#msgid_'+id+'_reply_holder' ).removeClass('opened');
                } else {
                    $( '#msgid_'+id+'_reply_holder' ).show(250);
                    // Set focus to the reply textarea.
                    $( '#msgid_'+id+'_reply_holder form textarea#reply' ).focus();
                    $( '#msgid_'+id+'_reply_holder' ).addClass('opened');
                }
                $( '#reply-error' ).detach();
                $( '#reply-error-icon' ).detach();
                break;
            case 'collapse':
                $( '#msgid_'+id ).find('i').fadeOut(300);
                $( '#msgid_'+id+'_details' ).animate({
                    marginLeft: '-=30'
                });
                // Hide the replies section, the reply form and button and the original message.
                $( '#action_reply_'+id ).slideUp(250);
                $( '#msgid_'+id+'_replies').hide(750);
                $( '#msgid_'+id+'_reply_holder').hide(300);
                $( '#msgid_'+id+'_reply_holder').removeClass('opened');
                $( '#origmessage_'+id).hide(750);
                $( '#msgid_'+id+'_details' ).removeClass('opened');
                // Now show all the other messageholder div's.
                $( '.messageholder-main' ).show(500);
                // Enable the toggle button.
                $( '#toggle_categories' ).attr('disabled', false);
                toggleStatus = 0;
                break;
            case 'close':
                // Set the status of this message to closed.
                var promises = ajax.call([
                    { methodname: 'local_capdmhelpdesk_update_message', args:{ msgid: id, field: 'status', val: 1 } }
                ]);
                promises[0].done(function(data) {
                // On completion look up the language string and display a message on screen.
                    $.when(str.get_string('messageclosed', 'local_capdmhelpdesk')).done(function(localizedEditString) {
                        capdmhelpdesk_alert_msg(localizedEditString, 0);
                    });
                    $( '#msgid_'+id+' div.status' ).removeClass('status_0');
                    $( '#msgid_'+id+' div.status' ).addClass('status_1');
                }).fail(notification.exception);
                break;
        }

    });

    $( 'body' ).on('click', '.toggle', function(){

        var toggleID = $( this ).attr('id').split('_')[1];
        var toggle0 = $( this ).attr('id').split('_')[0];

        switch(toggleID){

            case "compact":
                if( toggleStatus ){

                    capdmhelpdesk_reset_messagedetails_heights();
                    // Delete the objHeights object so it does not cause confusion.
                    toggleStatus = 0;
                } else {
                    // Get the original heights and hold them in an object
                    $( 'div.messageholder-main' ).each(function(){
                        objHeights[$( this ).attr('id')] =  $( this ).height();
                    });
                    // Set the toggle status value.
                    toggleStatus = 1;
                    // Hide all the messagedetails_content sections.
                    $( 'div.messageholder-main' ).animate({
                        height: 60
                    }, 300);
                }
                break;
            case "categories":
                if( $( '#categories_detail' ).is( ':visible' )){
                    $( '#categories_detail' ).fadeOut(300);
                    $( ".category" ).fadeIn(300);
                } else {
                    $( '#categories_detail' ).fadeIn(300);
                }
                break;
            default:
                switch(toggle0){
                    case "cat":
                        $( ".category" ).fadeOut(300);
                        $( ".cat_"+toggleID ).fadeToggle(300);
                        break;
                }
        }

    });

    // Toggle the search box
    $( 'body' ).on('click', '#search', function(){
        if($( '#searchboxholder' ).hasClass('open')){
            capdmhelpdesk_clear_search();
            $( '#searchboxholder' ).slideUp(300);
            $( '#searchboxholder' ).removeClass('open');
            $( '#searchresultsholder' ).slideUp(300);
        } else {
            $( '#searchboxholder' ).slideDown(300);
            $( '#searchboxholder' ).addClass('open');
            $( '#searchresultsholder' ).slideDown(300);
            $( '#searchbox' ).focus();
        }
    });

    // Ajax live search
    $( 'body' ).on('keyup', '#searchbox', function(){
// galgalajax
        var d = new Date();
        var n = d.getTime();
        var searchStr = $( '#searchbox' ).val();

        // Stop any pending searches.
        $( this ).clearQueue();

        var promises = ajax.call([
                    { methodname: 'local_capdmhelpdesk_search', args:{ param: searchStr} }
                ]);
        promises[0].done(function(data) {
            // We have the data - lets re-render the template with it.
            templates.render('local_capdmhelpdesk/search_results', data).done(function(html, js) {
                $('[data-region="searchresults"]').replaceWith(html);

                var noResults = $( '.search-item').length;
                var totalY = 0;
                var baseY = 0;
                var padding = $( '#searchresults' ).css('paddingTop') + $( '#searchresults' ).css('paddingBottom');

                $( '.search-item' ).each(function(i){
                    totalY += $( this ).outerHeight();
                    if(baseY == 0){
                        baseY = totalY;
                    }
                });

                setTimeout(function(){ $( '#searchresults' ).show(300); }, 500);


                //var newSearchBoxY = $( '#searchresults' ).height();
                //$( '#searchboxholder' ).animate({ height: newSearchBoxY + baseY + padding }, 200);

                // And execute any JS that was in the template.
                templates.runTemplateJS(js);
            }).fail(notification.exception);
            }).fail(notification.exception);
    });


    $( 'body' ).on('click', '#test', function(){

            var errString = '';
            $.when(str.get_string('newmessageadded', 'local_capdmhelpdesk')).done(function(localizedEditString) {
                console.log(localizedEditString);
            });



    });

    // End of listeners.



    // #########################################################################
    //
    // Functions go here
    //
    // #########################################################################

    // This function clears the search results.
    function capdmhelpdesk_clear_search(){

        $( '#searchbox' ).val('');
        $.when(str.get_string('searchtitle', 'local_capdmhelpdesk')).done(function(localizedEditString) {
            $( '#searchresults' ).html(localizedEditString);
        });
    }


    // This function takes the URL querystring if there is one and breaks it down to a JS obj for later use.
    function capdmhelpdesk_get_querystring(){

        var qString = document.URL.split('?')[1];
        if(qString){
            var params = qString.split('&');
            var i;
            for(i = 0; i < params.length; i++){
                var k = params[i].split('=')[0];
                var v = params[i].split('=')[1];
                objQS[k] = v;
            }
        }
    }

    // This function resets the div.messagedetails_content heights.
    function capdmhelpdesk_reset_messagedetails_heights(){

        // Uses the global object objHeights
        $.each(objHeights, function(k, v){
                $( '#'+k ).animate({
                    height: v
                }, 300, function(){
                    // Now remove the height CSS setting as it restricts other interaction.
                    $( '#'+k ).css('height','');
                });
        });
    }


    // Use a function to validate the reply forms so it can be reused on dynamic reload of messages.
    function capdmhelpdesk_set_reply_validation(){

            $.when(str.get_string('enterreplytext', 'local_capdmhelpdesk')).done(function(localizedEditString) {
                var en = {required: localizedEditString};

            $('.form_reply_message').each(function () {

                $.extend($.validator.messages, en);

                $(this).validate({
                    rules: {
                        reply: {required: true,
                            }
                    },
                    errorElement: "em",
                    errorPlacement: function ( error, element ) {
                        // Add the `help-block` class to the error element
                        error.addClass( "help-block" );

                        // Add `has-feedback` class to the parent div.form-group
                        // in order to add icons to inputs
                        element.parents( ".col-sm-5" ).addClass( "has-feedback" );

                        if ( element.prop( "type" ) === "checkbox" ) {
                                error.insertAfter( element.parent( "label" ) );
                        } else {
                                error.insertAfter( element );
                        }

                        // Add the span element, if doesn't exists, and apply the icon classes to it.
                        if ( !element.next( "span" )[ 0 ] ) {
                            $( "<span id='reply-error-icon' class='icon fa fa-times form-control-feedback'></span>" ).insertAfter( element );
                        }
                    },
                    success: function ( label, element ) {
                        // Add the span element, if doesn't exists, and apply the icon classes to it.
                        if ( !$( element ).next( "span" )[ 0 ] ) {
                            $( "<span id='reply-error-icon' class='icon fa fa-check form-control-feedback'></span>" ).insertAfter( $( element ) );
                        }
                    },
                    highlight: function ( element, errorClass, validClass ) {
                        $( element ).parents( ".col-sm-5" ).addClass( "has-error" ).removeClass( "has-success" );
                        $( element ).next( "span" ).addClass( "fa-times" ).removeClass( "fa-check" );
                    },
                    unhighlight: function ( element, errorClass, validClass ) {
                        $( element ).parents( ".col-sm-5" ).addClass( "has-success" ).removeClass( "has-error" );
                        $( element ).next( "span" ).addClass( "fa-check" ).removeClass( "fa-times" );
                    },
                    submitHandler: function ( form ) {

                        // Hide the form and show an information message as sending emails can cause a delay
                        toggleNewForm(0, 'reply');
                        var frm = $( form ).attr('id');
                        var replyMsg = $( '#'+frm+' #reply').val();
                        var replyTo = $( '#'+frm+' #replyto').val();
                        var replierId = $( '#'+frm+' #replierid').val();
                        var notify = $( '#'+frm+' #notify').val();
                        var owner = $( '#'+frm+' #owner').val();
                        var subject = $( '#'+frm+' #subject').val();
                        // Ugly hack to force the default value.
                        if($( '#'+frm+' #autoclose' )[0].checked ){
                            status = -1;
                        } else {
                            status = 0;
                        }

                        // Disable the submit button to prevent multiple submits.
                        $( '#reply_message_submit' ).attr('disabled', true);
                        $( '#form_reply_message_'+replyTo ).hide(300);

                        var promises = ajax.call([
                            { methodname: 'local_capdmhelpdesk_save_reply', args:{ replyto: replyTo, message: replyMsg, replierid: replierId, notify: notify, owner: owner, subject: subject, status: status} },
                            { methodname: 'local_capdmhelpdesk_get_replies', args:{ replyto: replyTo } }
                        ]);
                        promises[0].done(function(data) {
                            // Submit the reply and feedback to the user
                            $( '#msgid_'+replyTo+'_reply_holder' ).hide(750);
                            $( '#msgid_'+replyTo+'_reply_holder #form_reply_message #reply' ).text('');

                            // Empty the contents of the reply textarea.
                            $( '#reply' ).val('');

                            // Enable the submit button again.
                            $( '#reply_message_submit' ).attr('disabled', false);
                            $( '#form_reply_message_'+replyTo ).show(300);
                            $( '#msgid_'+replyTo+'_reply_holder' ).removeClass('opened');
                            $( '#reply-error' ).detach();
                            $( '#reply-error-icon' ).detach();

                            // Reset the info message.
                            toggleNewForm(1, 'reply');

                        }).fail(notification.exception);

                        // Now reload the replies for this message
                        promises[1].done(function(data) {
                            // We have the data - lets re-render the template with it.
                            templates.render('local_capdmhelpdesk/message_replies', data).done(function(html, js) {
                                $('[data-region="msgid_'+replyTo+'_replies"]').replaceWith(html);
                                // And execute any JS that was in the template.
                                templates.runTemplateJS(js);
                            }).fail(notification.exception);
                        }).fail(notification.exception);

                        // Always return false so the form does not submit as we want to use Ajax.
                        return false;
                    }
                });
            });
        });
    }

    /**
    *	Function to toggle visibility of new message holding message
    *
    *	@param direction    = show (1) or hide (0) the new message form.
    *	@param item         = whether this is the new message or reply form.
    *
    */
    function toggleNewForm(direction, item){

        var msg = $( '#'+item+'_message_waiting' );
        var form = $( '#'+item+'_message_form' );

        switch(direction){
            case 0:
                form.hide(300);
                msg.show(300);
                break;
            case 1:
                msg.hide(300);
                form.show(300);
                break;
        }
    }

    /**
    *	Function to set and display a helpdesk feedback message
    *
    *	@param msg    = Text of the message to show.
    *	@param opt    = -1 hides the message box otherwise it shows.
    *
    */
    function capdmhelpdesk_alert_msg(msg, opt){

        $('#capdmhelpdesk-feedback').hide(500);

        switch(opt){
            case -1:
                    $('#capdmhelpdesk-feedback').hide(500);
                    break;
            default:
                    $('#capdmhelpdesk-feedback').html(msg);
                    $('#capdmhelpdesk-feedback').show(500);
        }
    }

    return {
        init: function() {

            // Parse the querystring if there is one and if msgid is in the querystring then try viewing that message.
            // If the message does not exist, either at all or for this user then report accordingly.
            capdmhelpdesk_get_querystring();
            if(Object.keys(objQS).length > 0 && 'msgid' in objQS){
                if( $( '#messagedetails_'+objQS.msgid).length ){
                    $( '#messagedetails_'+objQS.msgid).trigger('click');
                } else {
                    $.when(str.get_string('notallowedtoviewid', 'local_capdmhelpdesk')).done(function(localizedEditString) {
                        capdmhelpdesk_alert_msg(localizedEditString, 0);
                    });
                }
            }

            // If there are helpdesk messages then hide the new message click prompt
            // otherwise show this as a tooltip.
            if( $( '.messageholder-main' ).length > 0 ){
                $( '#newmessageprompt' ).addClass('hidden');
            } else {
                // Animate the tooltip
                $( '#newmessageprompt' ).animate({
                    left: "-=30",
                    opacity: 1
                }, 450, function(){
                    // Now fade out after a pause.
                    setTimeout(function(){
                        $( '#newmessageprompt').fadeOut(1000);
                    }, 2000);
                });
            }

            if( userType === 'student' ){
                $( '#show_open' ).trigger('click');
                $( '#show_open' ).focus();
            }
        }
    };
});