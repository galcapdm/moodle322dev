<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains a custom renderer class used by the capdmcontactus module.
 *
 * @package capdmcontactus
 * @copyright 2013 CAPDM Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A custom renderer class that extends the plugin_renderer_base and
 * is used by the capdmcontactus module.
 *
 * @package mod-capdmcontactus
 * @copyright 2012 CAPDM Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class local_capdmcontactus_renderer extends plugin_renderer_base {

	/**
	*	This function displays a particular record
	*
	*	@param $id = id number of record to be viewed
	*	@return formatted HTML string
	*/
	public function capdmcontactus_display_message($id){

		global $DB;

		$output = '';
		
		// only list completed orders
		$rec = $DB->get_record('capdmcontactus', array('id'=>$id));

		$output .= html_writer::tag('h3', html_writer::tag('strong', get_string('contact_message','local_capdmcontactus').$id));
		
		$table = new html_table();
		$table->attributes = array('class'=>'capdmcontactus_message_table');
//		$table->head = array(get_string('orderno','local_capdmcontactus'),get_String('totalextax','local_capdmcontactus'), get_string('totalinctax', 'local_capdmcontactus'), get_string('dateoforder', 'local_capdmcontactus'), get_string('vieworderdetail', 'local_capdmcontactus'));
	
		// Name row
		$row = new html_table_row();
		$cell1 = new html_table_cell();
		$cell1->text = html_writer::tag('p', get_string('name', 'local_capdmcontactus'));
		$cell1->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'right');
		$cell2 = new html_table_cell();
		$cell2->text = html_writer::tag('p', $rec->fname.' '.$rec->lname);
		$cell2->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'left');
		$row->cells = array($cell1, $cell2);
		$table->data[] = $row;
		// Email row
		$row = new html_table_row();
		$cell1 = new html_table_cell();
		$cell1->text = html_writer::tag('p', get_string('email', 'local_capdmcontactus'));
		$cell1->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'right');
		$cell2 = new html_table_cell();
		$cell2->text = html_writer::tag('p', $rec->email);
		$cell2->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'left');
		$row->cells = array($cell1, $cell2);
		$table->data[] = $row;
		// Call me row
//		$row = new html_table_row();
//		$cell1 = new html_table_cell();
//		$cell1->text = html_writer::tag('p', get_string('callme', 'local_capdmcontactus'));
//		$cell1->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'right');
//		$cell2 = new html_table_cell();
//		$cell2->text = html_writer::tag('p', $rec->callme);
//		$cell2->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'left');
//		$row->cells = array($cell1, $cell2);
//		$table->data[] = $row;
		// date row
		$row = new html_table_row();
		$cell1 = new html_table_cell();
		$cell1->text = html_writer::tag('p', get_string('date', 'local_capdmcontactus'));
		$cell1->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'right');
		$cell2 = new html_table_cell();
		$cell2->text = html_writer::tag('p', date('l jS \of F Y H:i:s', $rec->cdate));
		$cell2->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'left');
		$row->cells = array($cell1, $cell2);
		$table->data[] = $row;
		// subject row
		$row = new html_table_row();
		$cell1 = new html_table_cell();
		$cell1->text = html_writer::tag('p', get_string('subject', 'local_capdmcontactus'));
		$cell1->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'right');
		$cell2 = new html_table_cell();
		$cell2->text = html_writer::tag('p', $rec->subject);
		$cell2->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'left');
		$row->cells = array($cell1, $cell2);
		$table->data[] = $row;
		// message row
		$row = new html_table_row();
		$cell1 = new html_table_cell();
		$cell1->text = html_writer::tag('p', get_string('message', 'local_capdmcontactus'));
		$cell1->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'right');
		$cell2 = new html_table_cell();
		$cell2->text = html_writer::tag('p', $rec->message);
		$cell2->attributes = array('class'=>'capdmcontactus_cell', 'align'=>'left');
		$row->cells = array($cell1, $cell2);
		$table->data[] = $row;

		$output .= html_writer::table($table);

		return $output;
	}
	
	
// ==========================================================================================================================================























	/**
	*	This function displays an infosection block - each info section block is associated with one or more pages
	*
	*	@return string
	*/
	public function capdmcontactus_all_details(){
		
		global $CFG, $DB, $USER;
	
		$info = $DB->get_records_sql('select * from {capdmcontactus_config} where display = ? order by type', array('1'));

		$output = '';
		
		if($info){
	
			$output .= html_writer::start_tag('div', array('id'=>'capdmcontactus_holder'));
			
			if(!isset($USER->lang)){
				$lang = 'en';
			} else {
				$lang = $USER->lang;
			}
	
			if($info){
	
				$last_type ='';
				$x = 1;
				foreach($info as $i){		
					if(strpos($i->content, '~~') > -1){
						$content = str_replace('~~','<br />', $i->content);
					} else {
						$content = $i->content;
					}
					
					if($i->label){
						$label = $i->label.' - ';
					} else {
						$label = $i->label;
					}
	
					if($last_type != $i->type){
						if($x > 1){
							$output .= html_writer::end_tag('div');	
						}
						$output .= html_writer::start_tag('div', array('class'=>'capdmcontactus_block'));
						$output .= html_writer::tag('h3', get_string($i->type, 'local_capdmcontactus'));
					}
					if($i->type == 'email'){
						$output .= html_writer::tag('p', $label.html_writer::link('mailto://'.$i->content, $content));
					} else {
						$output .= html_writer::tag('p', $label.$content);
					}
					$last_type = $i->type;
					$x++;
				}					
				$output .= html_writer::end_tag('div');	
			}
			$output .= html_writer::end_tag('div');
		}
		return $output;
	}

	
	public function order_added(){
	
		global $DB;
		
		$output = '';
		
		$output .= html_writer::start_tag('div', array('id'=>'infomessage','class'=>'highlight'));
		$output .= html_writer::tag('p', get_string('addthankyou','local_capdmcontactus'));
		$output .= html_writer::end_tag('div');

		return $output;
	}

	/**
	*	This function displays the list of available courses
	*
	*	@return string
	*/
	public function get_courses(){

		global $DB, $USER, $CFG;
		
		$output = '';
		
		$courses = $DB->get_records_sql('SELECT cc.id, cat.id as cat_id, cat.name as cat_name, cat.sortorder as cat_sort, c.sortorder as course_sort, c.fullname FROM mdl_capdmcontactus_course cc inner join mdl_course c on cc.course_id = c.id inner join mdl_course_categories cat on c.category = cat.id where cc.publish = ? order by cat_sort, course_sort', array('publish'=>1));

		$programmes = $DB->get_records_sql('SELECT cc.id, 0 as cat_id, \'Porgrammes\' as cat_name, 0 as cat_sort, 0 as course_sort, cc.fullname FROM mdl_capdmcontactus_course cc where cc.publish = 1 and type = ? order by cat_sort, course_sort', array('type'=>'p'));
		
		$progsandcourses = array_merge($courses, $programmes);

		// get a list of this user's courses so we can indicate they are enrolled
		$arrMyCourses = get_mycourses($USER->id);

		$lastcat = '';
		$startdetail = false;
		$x = 0;
	
		foreach($progsandcourses as $course){
			if($course->cat_id != $lastcat){
				if($x > 0){
					$output .= html_writer::end_tag('div');				
				}
				$output .= html_writer::start_tag('div', array('id'=>'toggle_course_'.$course->id, 'class'=>'category_header more'));
				$output .= html_writer::tag('h2', $course->cat_name);
				$output .= html_writer::end_tag('div');
				$x ++;
				$output .= html_writer::start_tag('div', array('id'=>'toggle_course_'.$course->id.'_detail', 'class'=>'category_detail capdmcontactus_info_section'));
			}
			// if already enrolled on a course then indicate it
				$output .= html_writer::start_tag('p');
				if(in_array($course->id, $arrMyCourses)){
					$output .= html_writer::link($CFG->wwwroot.'/course/view.php?id='.$course->id, html_writer::empty_tag('img', array('class'=>'coursepiclink', 'src'=>'pix/enrolled.png', 'title'=>get_string('enrolled','local_capdmcontactus'), 'alt'=>get_string('enrolled','local_capdmcontactus'))));
				} else {
					$output .= html_writer::empty_tag('img', array('class'=>'coursepiclink', 'src'=>'pix/notenrolled.png', 'alt'=>'spacer'));
				}
				$output .= html_writer::link('view.php?id='.$course->id, $course->fullname, array('class'=>'courselink', 'title'=>get_string('viewcourseinformation','local_capdmcontactus')));
				$output .= html_writer::end_tag('p');

			$lastcat = $course->cat_id;
		}

		$output .= html_writer::end_tag('div');

$output .= '<script type="text/javascript">
	YUI().use("node", function(Y) {
	  Y.all(".capdmcontactus_info_section").hide(true);
	  Y.all(".capdmcontactus_info_section").toggleClass(\'visible\');
	});
	
	YUI().use(\'transition\', \'node-event-delegate\', \'cssbutton\', function(Y) {
		Y.delegate(\'click\', function(e) {
			var buttonID = e.currentTarget.get(\'id\');
			
			node = Y.one(\'#\'+buttonID+\'_detail\');
			node.toggleClass(\'visible\');

			if (node.hasClass(\'visible\')) {
				node.hide(true);
				Y.one(\'#\'+buttonID).removeClass(\'less\');
				Y.one(\'#\'+buttonID).addClass(\'more\');
			} else {
				node.show(true);
				Y.one(\'#\'+buttonID).removeClass(\'more\');
				Y.one(\'#\'+buttonID).addClass(\'less\');
			}
	
		}, document, \'div\');
	});
</script>';

		return $output;	

	}

	/**
	*	This function displays the list of available programmes
	*
	*	@return string
	*/
	public function get_programmes(){

		global $DB, $USER, $CFG;
		
		$output = '';
		
		$programmes = $DB->get_records('capdmcontactus_programmes', array('publish'=>1));

		// get a list of this user's courses so we can indicate they are enrolled
//		$arrMyCourses = get_mycourses($USER->id);

//		$lastcat = '';
//		$startdetail = false;
//		$x = 0;

		$output .= html_writer::start_tag('div', array('id'=>'toggle_programmes', 'class'=>'category_header more'));
		$output .= html_writer::tag('h2', get_string('capdmcontactus_programmes','local_capdmcontactus'));
		$output .= html_writer::end_tag('div');

		$output .= html_writer::start_tag('div', array('id'=>'toggle_programmes_detail', 'class'=>'category_detail capdmcontactus_info_section'));	

		foreach($programmes as $p){
			$output .= html_writer::start_tag('p');
			$output .= html_writer::link('view.php?id='.$p->id, $p->prog_name, array('class'=>'courselink', 'title'=>get_string('viewcourseinformation','local_capdmcontactus')));
			$output .= html_writer::end_tag('p');
		}

		$output .= html_writer::end_tag('div');
		return $output;	

	}

	/**
	*	This function displays a list of orders for a particular user
	*
	*	@param $user_id = userid to use for listing the orders
	*	@return string
	*/
	public function list_orders($user_id){

		global $DB, $CFG, $USER;

		$output = '';
		
		// only list completed orders
		$orders = $DB->get_records('capdmcontactus_order', array('user_id'=>$user_id, 'order_status'=>'c'));

		$output .= html_writer::tag('h1', get_string('yourorders','local_capdmcontactus'));
		$output .= html_writer::tag('p', get_string('yourordersinfo','local_capdmcontactus'));
		
		// if there are no completed orders then display a message accordingly
		if(sizeof($orders) < 1){
			$output .= $this->no_order('c');
			return $output;
		}
		
		$table = new html_table();
		$table->attributes = array('class'=>'order_summary_table');
		$table->head = array(get_string('orderno','local_capdmcontactus'),get_String('totalextax','local_capdmcontactus'), get_string('totalinctax', 'local_capdmcontactus'), get_string('dateoforder', 'local_capdmcontactus'), get_string('vieworderdetail', 'local_capdmcontactus'));
	
		$arrFields = array();
		$arrEntries = array();

		foreach($orders as $o){
			$order_no = $o->id;
			$total_inc_tax = $o->order_total;
			$total_ex_tax = $o->order_subtotal;
			$dateoforder = date('l, j F, Y', $o->mdate);

			$row = new html_table_row();

			$cell1 = new html_table_cell();
			$cell1->text = html_writer::tag('p', $order_no);					
			$cell1->attributes = array('class'=>'oddcell', 'align'=>'left');
			
			$cell2 = new html_table_cell();
			$cell2->text = html_writer::tag('p', format_money($total_inc_tax, $o->order_currency));
			$cell2->attributes = array('class'=>'evencell', 'align'=>'left');

			$cell3 = new html_table_cell();
			$cell3->text = html_writer::tag('p', format_money($total_ex_tax, $o->order_currency));
			$cell3->attributes = array('class'=>'oddcell', 'align'=>'left');

			$cell4 = new html_table_cell();
			$cell4->text = html_writer::tag('p', $dateoforder);
			$cell4->attributes = array('class'=>'evencell', 'align'=>'left');

			$cell5 = new html_table_cell();
			$params = array('orderno'=>$order_no);
			$cell5->text = html_writer::tag('span', '<form class="orderlistbutton" autocomplete="off" action="view.php?id=-1" method="post" accept-charset="utf-8" id="mform'.$order_no.'">
			<div style="display: none;"><input name="orderid" type="hidden" value="'.$order_no.'">
			<input name="thisaction" type="hidden" value="91">
			<input name="sesskey" type="hidden" value="'.$USER->sesskey.'">
			<input name="_qf__local_capdmcontactus_list_order_form" type="hidden" value="1">
			</div>
			<fieldset class="hidden"><div>
				<div id="fgroup_id_buttonar'.$order_no.'" class="fitem fitem_actionbuttons fitem_fgroup">
					<div class="felement fgroup">
						<input class="vieworderbutton" title="'.get_string('viewordertitle','local_capdmcontactus', $params).'" name="submitbutton" value="" type="submit" id="id_submitbutton'.$order_no.'"></div></div>
					</div>
			</fieldset>
			</form>');
			$cell5->attributes = array('class'=>'oddcell', 'align'=>'left');

			$row->cells = array($cell1, $cell2, $cell3, $cell4, $cell5);
			$table->data[] = $row;
		}

		$output .= html_writer::table($table);		

//			$output .= html_writer::start_tag('div', array('id'=>'capdmcontactus_no_order_holder', 'class'=>'highlight'));		
//			$output .= html_writer::tag('p', get_string('orderplaced', 'local_capdmcontactus', array('orderdate'=>date('l, j F, Y', $o->mdate))));
//			$output .= html_writer::end_tag('div');


$output .= '<script type="text/javascript">
	YUI().use("node", function(Y) {
	  Y.all(".capdmcontactus_step_form").removeClass("mform");
	});
</script>';

		return $output;
	}

	/**
	*	This function displays details for a particular order number
	*
	*	@param $order_id = id number for a particular order
	*	@param $user_id = user id for added security
	*	@return string
	*/
	public function view_order_details($order_id, $user_id){

		global $DB;
		
		$output = '';

		$params = array('orderno'=>$order_id, 'userid'=>$user_id);
		$order = $DB->get_records_sql('select i.id, o.id as order_id, o.order_currency as currency, o.order_total, o.order_subtotal, o.order_tax, o.order_tax_details, tax.tax, o.coupon_discount as discount_total, o.coupon_code, o.mdate as order_date, crse.fullname, i.course_price FROM mdl_capdmcontactus_order o inner join mdl_capdmcontactus_order_item i on o.id = i.order_id inner join mdl_course crse on i.course_id = crse.id inner join mdl_capdmcontactus_tax tax on o.order_tax_details = tax.taxName where o.id = ? and user_id = ?', $params);
		
		if(!$order){
			$output .= capdmcontactus_error('bad_oder_details');
			return $output;
		}
		
		//  need to go over the record object to pick up these values - bit rubbish...there must be a better way!
		foreach($order as $item){
			$order_id = $item->order_id;
			$order_date = $item->order_date;
		}

		$infotable = new html_table();
		$infotable->attributes = array('class'=>'order_detail_table_top');
		
		// address row
		$row = new html_table_row();
		$row->attributes = array('class'=>'addresses');

		$cell_our_add = new html_table_cell();
		$cell_our_add->text = seller_address();
		$cell_our_add->attributes = array('class'=>'seller_address');

		$cell_our_details = new html_table_cell();
		$cell_our_details->text = seller_details();
		$cell_our_details->attributes = array('class'=>'seller_details');
	
		$row->cells = array($cell_our_add, $cell_our_details);
		$infotable->data[] = $row;
		
		// reference row
		$row = new html_table_row();
		$row->attributes = array('class'=>'addresses');

		$cell_our_ref = new html_table_cell();
		$cell_our_ref->text = get_string('ordernumber','local_capdmcontactus', $order_id);
		$cell_our_ref->attributes = array('class'=>'seller_address');

		$cell_order_date = new html_table_cell();
		$cell_order_date->text = get_string('orderdate','local_capdmcontactus', date('j F, Y', $order_date));
		$cell_order_date->attributes = array('class'=>'seller_details');
	
		$row->cells = array($cell_our_ref, $cell_order_date);
		$infotable->data[] = $row;
		
		
		$output .= html_writer::table($infotable);
				
		// now compile the detail information
		$table = new html_table();
		$table->attributes = array('class'=>'order_detail_table');
		$table->head = array(get_string('item','local_capdmcontactus'),get_String('total','local_capdmcontactus'));
	
		$arrFields = array();
		$arrEntries = array();
		$subTotal = 0;
		$reccount = 0;

		foreach($order as $item){

			$sub_total = $item->order_subtotal;
			$tax_total = $item->order_tax;
			$tax_detail = $item->order_tax_details;
			$order_total = $item->order_total;
			$discount_total = $item->discount_total;
			$discount_reason = $item->coupon_code;
			$currency = $item->currency;
			$order_tax_details = ($item->tax * 100).'%';

			$row = new html_table_row();

			$cell1 = new html_table_cell();
			$cell1->text = html_writer::tag('p', $item->fullname);
			$cell1->attributes = array('class'=>'items_cell');
			
			$cell2 = new html_table_cell();
			$cell2->text = html_writer::tag('p', format_money($item->course_price, $currency));
			$cell2->attributes = array('class'=>'totals_cell');

			$row->cells = array($cell1, $cell2);
			$table->data[] = $row;
			$reccount ++;
		}
		

		$row = new html_table_row();

		$cell1 = new html_table_cell();
		$cell1->text = html_writer::tag('p', '');
		$cell2 = new html_table_cell();
		$cell2->text = html_writer::tag('p', '');
			
		// if there are only a few items then the invoice would look silly beintg short so insert a variable size spacer
		switch($reccount){
			case 1:
			case 2:
			case 3:
			case 4:
				$cell1->attributes = array('class'=>'items_cell invoice_spacer_large');
				$cell2->attributes = array('class'=>'items_cell invoice_spacer_large');
				break;
			case 5:
			case 6:
			case 7:
			case 8:
				$cell1->attributes = array('class'=>'items_cell invoice_spacer_small');
				$cell2->attributes = array('class'=>'items_cell invoice_spacer_small');
				break;
		}

		$row->cells = array($cell1, $cell2);
		$table->data[] = $row;

				
		// discount row
		$row = new html_table_row();
		$row->attributes = array('class'=>'totaltop');

		$cell_disc_label = new html_table_cell();
		$cell_disc_label->text = html_writer::tag('p', get_string('discount_total','local_capdmcontactus',$discount_reason));
		$cell_disc_label->attributes = array('class'=>'items_cell disctotal');

		$cell_disc = new html_table_cell();
		$cell_disc->text = html_writer::tag('p', '-'.format_money($discount_total, $currency));
		$cell_disc->attributes = array('class'=>'totals_cell disctotal');
	
		$row->cells = array($cell_disc_label, $cell_disc);
		$table->data[] = $row;

		// sub total row
		$row = new html_table_row();

		// indicate whether this has been taxed or not
		if($order_tax_details){
			$ex_tax_detail = get_string('ex_tax', 'local_capdmcontactus',$tax_detail);
		}

		$cell_sub_label = new html_table_cell();
		$cell_sub_label->text = html_writer::tag('p', get_string('sub_total','local_capdmcontactus', $ex_tax_detail));
		$cell_sub_label->attributes = array('class'=>'items_cell subtotal');

		$cell_sub = new html_table_cell();
		$cell_sub->text = html_writer::tag('p', format_money($sub_total, $currency));
		$cell_sub->attributes = array('class'=>'totals_cell subtotal');
	
		$row->cells = array($cell_sub_label, $cell_sub);
		$table->data[] = $row;

		// tax row
		$row = new html_table_row();

		$cell_tax_label = new html_table_cell();
		$cell_tax_label->text = html_writer::tag('p', get_string('tax_total','local_capdmcontactus', $order_tax_details));
		$cell_tax_label->attributes = array('class'=>'items_cell taxtotal');

		$cell_tax = new html_table_cell();
		$cell_tax->text = html_writer::tag('p', format_money($tax_total, $currency));
		$cell_tax->attributes = array('class'=>'totals_cell taxtotal');
	
		$row->cells = array($cell_tax_label, $cell_tax);
		$table->data[] = $row;

		// total row
		$row = new html_table_row();
		$row->attributes = array('class'=>'grandtotal');

		// indicate whether this has been taxed or not
		if($order_tax_details){
			$inc_tax_detail = get_string('inc_tax', 'local_capdmcontactus',$tax_detail);
		}
		
		$cell_tot_label = new html_table_cell();
		$cell_tot_label->text = html_writer::tag('p', get_string('order_total','local_capdmcontactus', $inc_tax_detail));
		$cell_tot_label->attributes = array('class'=>'items_cell totaltotal');

		$cell_tot = new html_table_cell();
		$cell_tot->text = html_writer::tag('p', format_money($order_total, $currency));
		$cell_tot->attributes = array('class'=>'totals_cell totaltotal');
	
		$row->cells = array($cell_tot_label, $cell_tot);
		$table->data[] = $row;


		$output .= html_writer::start_tag('div', array('id'=>'order_paid'));
		$output .= html_writer::table($table);
		$output .= html_writer::end_tag('div');
		
		return $output;
	}

	/**
	*	This function displays the main course information
	*
	*	@param $id = id no of an available course
	*	@return string
	*/
	public function no_order($status){

		$output = '';
		
		$output .= html_writer::start_tag('div', array('id'=>'capdmcontactus_no_order_holder', 'class'=>'highlight'));		
		switch($status){
			case 'p':  // no open orders
				$output .= html_writer::tag('p', get_string('nopendingorder','local_capdmcontactus'));
				break;
			case 'c':  // no complete orders
				$output .= html_writer::tag('p', get_string('nocompleteorder','local_capdmcontactus'));
				break;
		}
		$output .= html_writer::end_tag('div');
		
		return $output;
	}


	/**
	*	This function displays the main course information
	*
	*	@param $id = id no of an available course
	*	@return string
	*/
	public function get_course_detail($id, $publish = 1){

		global $DB, $USER, $CFG;

		if($USER->id > 0){
			$country = $USER->country;
		} else {
			$country = 'GB';
		}

		$output = '';
		
		$params = array($id, $publish);
//    	$course = $DB->get_record_sql('select crse.id, crse.fullname, crse.shortname, crse.idnumber, cc.id as order_id, cc.course_format, cc.award, cc.awarding_body, cc.study_time, cc.experience, cc.course_description, cc.course_details from mdl_capdmcontactus_course cc left join mdl_course crse on cc.course_id = crse.id where cc.course_id = ? and cc.publish = ?', $params, '*', MUST_EXIST);
    	$course = $DB->get_record_sql('select crse.id, crse.fullname, crse.shortname, crse.idnumber, cc.id as order_id, cc.course_format, cc.award, cc.awarding_body, cc.study_time, cc.experience, cc.course_description, cc.course_details from mdl_capdmcontactus_course cc left join mdl_course crse on cc.course_id = crse.id where cc.id = ? and cc.publish = ?', $params, '*', MUST_EXIST);
		
		$fees = $DB->get_records('capdmcontactus_fees', array('capdmcontactus_course_id'=>$course->order_id));

		$outfee = '';
		foreach($fees as $fee){
			$outfee .= $fee->currency.' '.$fee->fee.'<br>';
		}

		// Detail holder - left panel
		$output .= html_writer::start_tag('div', array('id' => 'capdmcontactus_left_panel', 'class'=>'yui3-skin-sam'));
		
		$output .= html_writer::tag('h1', $course->fullname);
		
		$output .= '<div id="course_description">';
			$output .= '<div id="description">'.$course->course_description.'</div>';
		$output .= '</div>';
	
		$output .= html_writer::start_tag('div', array('id'=>'toggle_details', 'class'=>'capdmcontactus_menu_button'));
		$output .= html_writer::tag('p', 'Details', array('id'=>'toggle_details','class'=>'capdmcontactus_menu_item'));
		$output .= html_writer::end_tag('div');
		$output .= html_writer::start_tag('div', array('id'=>'toggle_details_detail', 'class'=>'capdmcontactus_info_section'));
			$output .= html_writer::tag('p', $course->course_details);
			$output .= html_writer::start_tag('div');
			$output .= html_writer::tag('p',get_string('topofpage','local_capdmcontactus'), array('class'=>'visible scrolltotop'));
			$output .= html_writer::end_tag('div');
		$output .= html_writer::end_tag('div');
		
		$output .= html_writer::start_tag('div', array('id'=>'toggle_fees', 'class'=>'capdmcontactus_menu_button'));
		$output .= html_writer::tag('p',  get_string('capdmcontactus_course_fees','local_capdmcontactus'), array('class'=>'capdmcontactus_menu_item'));
		$output .= html_writer::end_tag('div');
		$output .= html_writer::start_tag('div', array('id'=>'toggle_fees_detail', 'class'=>'capdmcontactus_info_section'));
			$output .= html_writer::tag('p', get_string('coursefeeinfo','local_capdmcontactus', format_money(get_course_price($id, $country), $country)));
			$output .= html_writer::tag('p', 'Need a VAT statement/indication here');
			$output .= html_writer::start_tag('div');
			$output .= html_writer::tag('p',get_string('topofpage','local_capdmcontactus'), array('class'=>'visible scrolltotop'));
			$output .= html_writer::end_tag('div');
		$output .= html_writer::end_tag('div');

		$output .= html_writer::start_tag('div', array('id'=>'toggle_award', 'class'=>'capdmcontactus_menu_button'));
		$output .= html_writer::tag('p', 'Awards', array('class'=>'capdmcontactus_menu_item'));
		$output .= html_writer::end_tag('div');
		$output .= html_writer::start_tag('div', array('id'=>'toggle_award_detail', 'class'=>'capdmcontactus_info_section'));
			$output .= html_writer::tag('p', 'Award info goes here');
			$output .= html_writer::start_tag('div');
			$output .= html_writer::tag('p',get_string('topofpage','local_capdmcontactus'), array('class'=>'visible scrolltotop'));
			$output .= html_writer::end_tag('div');
		$output .= html_writer::end_tag('div');

		$output .= html_writer::start_tag('div', array('id'=>'toggle_study', 'class'=>'capdmcontactus_menu_button'));
		$output .= html_writer::tag('p', get_string('capdmcontactus_duration','local_capdmcontactus'), array('class'=>'capdmcontactus_menu_item'));
		$output .= html_writer::end_tag('div');
		$output .= html_writer::start_tag('div', array('id'=>'toggle_study_detail', 'class'=>'capdmcontactus_info_section'));
			$output .= html_writer::tag('p', $course->study_time);
		$output .= html_writer::end_tag('div');
		
		$output .= html_writer::start_tag('div');
		$output .= html_writer::tag('p',get_string('topofpage','local_capdmcontactus'), array('class'=>'visible scrolltotop'));
		$output .= html_writer::end_tag('div');

$output .= '<script type="text/javascript">
	YUI().use("node", function(Y) {
	  Y.all(".capdmcontactus_info_section").hide(true);
	  Y.all(".capdmcontactus_info_section").toggleClass(\'visible\');
	  Y.all(".capdmcontactus_step_form").removeClass("mform");
	});
	

YUI().use(\'gallery-scrollintoview\', \'anim\', function(Y)
{
	var topofpage = Y.one(\'#page-header\');

	Y.all(\'.scrolltotop\').on(\'click\', function()
	{
		topofpage.scrollIntoView(
		{
			anim: true, duration: { value: 0.5 }
			
		});
	});


});
	
	YUI().use(\'transition\', \'node-event-delegate\', function(Y) {
		Y.delegate(\'click\', function(e) {
			var buttonID = e.currentTarget.get(\'id\');
			var node = Y.one(\'#\'+buttonID+\'_detail\');

//			node.toggleClass(\'visible\');
			Y.one(\'#\'+buttonID+\'_detail\').toggleClass(\'visible\');
			
			if (node.hasClass(\'visible\')) {
				node.hide(true);
			} else {
				node.show(true);
			}
	
		}, document, \'div\');
	});
</script>';

		// closing div for left panel
		$output .= html_writer::end_tag('div');
		
		// Right panel holder
		$output .= html_writer::start_tag('div', array('id' => 'capdmcontactus_right_panel'));

//		$output .= html_writer::start_tag('div', array('id'=>'enrol_single_payment', 'class'=>'capdmcontactus_menu_button'));
		if($USER->id > 0) {
			
// galgal			
//			$output .= html_writer::link($CFG->wwwroot.'/local/capdmcontactus/view.php?id='.$id.'&action=1', get_string('buycourse','local_capdmcontactus'), array('title'=>get_string('login','local_capdmcontactus')));

//			$form_courselist = new local_capdmcontactus_courselist_form('view.php?id=-1', null, 'post', '', array('class'=>'capdmcontactus_step_form'));
//			$form_courselist->display();
					
//			$form_buy = new local_capdmcontactus_buy_form('view.php?id=-1', array('action'=>1, 'itemid'=>$id), 'post', '', array('class'=>'capdmcontactus_step_form'));
//			$form_buy->display();
			
		} else {
			$loginstring = html_writer::link($CFG->wwwroot.'/login/index.php', get_string('login','local_capdmcontactus'), array('title'=>get_string('login','local_capdmcontactus')));
			$registerstring = html_writer::link($CFG->wwwroot.'/login/signup.php', get_string('register','local_capdmcontactus'), array('title'=>get_string('register','local_capdmcontactus')));
			$strings = array('login'=>$loginstring, 'register'=>$registerstring);
			$output .= html_writer::tag('p', get_string('logintobuy','local_capdmcontactus', $strings), array('class'=>'capdmcontactus_menu_item'));
		}
//		$output .= html_writer::end_tag('div');

		if($USER->id > 0 ){

			$params = array($USER->id);
			$orderItemCount = $DB->count_records_sql('SELECT count(i.id) as count_of_items FROM mdl_capdmcontactus_order o inner join mdl_capdmcontactus_order_item i on o.id = i.order_id where order_status = \'p\' and user_id = ?', $params);
			if($orderItemCount){
				$arrCount = array('count'=>$orderItemCount);

//				$output .= html_writer::start_tag('div', array('id'=>'view_order', 'class'=>'capdmcontactus_menu_button'));
//				$output .= html_writer::link($CFG->wwwroot.'/local/capdmcontactus/view.php?id=-1&action=2', get_string('vieworder','local_capdmcontactus', $arrCount), array('title'=>get_string('vieworder','local_capdmcontactus')));
//				$output .= html_writer::end_tag('div');
			}
		}


		// closing div for right panel
		$output .= html_writer::end_tag('div');
		
		
		
		return $output;
		
	}


	public function get_order($order_id, $item=NULL, $step){

		global $DB, $USER, $CFG;

		if($USER->id > 0){
			$country = $USER->country;
		} else {
			$country = 'GB';
		}

		$output = '';

		// check first to see if we need to update the order i.e. remove an item as quantity is not a factor here!
		if($item){
			$res = $DB->delete_records('capdmcontactus_order_item', array('id'=>$item));
		}

		$params = array('orderid'=>$order_id);

		$order = $DB->get_records_sql('select i.id, o.id as order_id, crse.fullname, i.course_price, o.coupon_discount, o.coupon_code from mdl_capdmcontactus_order o inner join mdl_capdmcontactus_order_item i on o.id = i.order_id inner join mdl_course crse on i.course_id = crse.id where o.id = ?', $params);

		if(sizeof($order) < 1){
			//  to do
			$output .= html_writer::start_tag('div');
			$output .= html_writer::tag('p', get_string('emptyorder','local_capdmcontactus'));
			$output .= html_writer::end_tag('div');
			return $output;
		}

		// Start order holder
		$output .= html_writer::start_tag('div', array('id'=>'capdmcontactus_order_holder'));
		
		switch($step){
			case 1:
				$output .= html_writer::tag('h1', get_string('order_summary_title','local_capdmcontactus'));
				$output .= html_writer::tag('p', get_string('order_summary','local_capdmcontactus'));
				break;
			case 2:
				$output .= html_writer::tag('h1', get_string('order_confirmation_title','local_capdmcontactus'));
				$output .= html_writer::tag('p', get_string('order_confirmation','local_capdmcontactus'));
				break;
			default:
				break;
		}

		if($order){

			$table = new html_table();
			$table->attributes = array('class'=>'order_table');
			$table->head = array(get_string('edit','local_capdmcontactus'),get_String('course_name','local_capdmcontactus'), get_string('course_fee', 'local_capdmcontactus'));
		
			$arrFields = array();
			$arrEntries = array();
			$subTotal = 0;

			foreach($order as $item){
				$order_id = $item->order_id;
				$coupon_discount = $item->coupon_discount;
				$couponCode = $item->coupon_code;

				$row = new html_table_row();
				$cell1 = new html_table_cell();
//				$cell1->text = html_writer::link('view.php?id=2&thisaction=2&item='.$item->id, get_string('remove','local_capdmcontactus')); $cell1->attributes = array('class'=>'leftcell', 'align'=>'left');
				$cell1->text = html_writer::tag('span', '<form class="removeitem" action="view.php?id=-1" method="post" id="mform'.$item->id.'">
				<div style="display: none;"><input name="orderid" type="hidden" value="'.$order_id.'">
				<input name="thisaction" type="hidden" value="2">
				<input name="delid" type="hidden" value="'.$item->id.'">
				<input name="sesskey" type="hidden" value="'.$USER->sesskey.'">
				<input name="_qf__local_capdmcontactus_list_order_form" type="hidden" value="1">
				</div>
				<fieldset class="hidden"><div>
					<div id="fgroup_id_buttonar'.$item->id.'" class="fitem fitem_actionbuttons fitem_fgroup">
						<div class="felement fgroup">
							<input class="delitem" title="'.get_string('remove','local_capdmcontactus').'" name="submitbutton" value="" type="submit" id="id_submitbutton'.$item->id.'"></div></div>
						</div>
				</fieldset>
				</form>');
				$cell2 = new html_table_cell();
				$cell2->text = $item->fullname; $cell2->attributes = array('class'=>'centercell', 'align'=>'left');
				$cell3 = new html_table_cell();
				$cell3->text = format_money($item->course_price, $country);    $cell3->attributes = array('class'=>'rightcell', 'align'=>'right');
				$row->cells = array($cell1, $cell2, $cell3);
				$table->data[] = $row;
				$subTotal += $item->course_price;
			}
		} else {
			$output .= html_writer::tag('p', get_string('emptyorder','local_capdmcontactus'));

		}

		// need to pass in the recently calculated subtotal to make sure the discount is correctly calculated
		$coupon_discount = calc_coupon_discount($order_id, $subTotal);
		
		// calculate the sub total minus the discount
//		$subTotal = number_format(($subTotal - $coupon_discount),2);
		$subTotal = ($subTotal - $coupon_discount);
		// TAX
		$tax_rate = get_tax_rate($country);
		// Order tax total
//		$taxTotal = number_format(($subTotal * $tax_rate->tax),2);
		$taxTotal = ($subTotal * $tax_rate->tax);
		// Order total
//		$orderTotal = number_format(($taxTotal + $subTotal),2);
		$orderTotal = ($taxTotal + $subTotal);

		// now update the order to reflect this
		update_order($order_id, $subTotal, $taxTotal, $tax_rate->taxname, $orderTotal, $coupon_discount);

		if($coupon_discount > 0){
			$arrTotals = array('couponDiscount'=>'-'.format_money($coupon_discount, $country), 'subTotal'=>format_money($subTotal, $country), 'taxTotal'=>format_money($taxTotal, $country), 'orderTotal'=>format_money($orderTotal, $country));
		} else {
			$arrTotals = array('subTotal'=>format_money($subTotal, $country), 'taxTotal'=>format_money($taxTotal, $country), 'orderTotal'=>format_money($orderTotal, $country));
		}

		$x = 1;
		foreach($arrTotals as $key=>$total){
		    $row = new html_table_row();
			if($x == 1){
				$row->attributes = array('class'=>'totaltop');
			}
		    $cell1 = new html_table_cell();
		    $cell1->text = ''; $cell1->attributes = array('class'=>'totalrow', 'align'=>'right');
		    $cell2 = new html_table_cell();
			if($key == 'taxTotal'){
			    $cell2->text = get_string($key,'local_capdmcontactus').' ('.($tax_rate->tax*100).'% '.$tax_rate->taxname.')'; $cell2->attributes = array('class'=>'totalrow', 'align'=>'right');
			} else {
				if($key == 'couponDiscount'){
					$cell2->text = get_string($key,'local_capdmcontactus').' ("'.$couponCode.'")'; $cell2->attributes = array('class'=>'totalrow', 'align'=>'right');
				} else {
				    $cell2->text = get_string($key,'local_capdmcontactus'); $cell2->attributes = array('class'=>'totalrow', 'align'=>'right');
				}
			}
		    $cell3 = new html_table_cell();
		    $cell3->text = $total;    $cell3->attributes = array('class'=>'totalrow', 'align'=>'right');
		    $row->cells = array($cell1, $cell2, $cell3);
		    $table->data[] = $row;
			$x ++;
		}
	    
		$output .= html_writer::table($table);

//		$output .= html_writer::start_tag('div', array('id'=>'enrol_single_payment', 'class'=>'capdmcontactus_menu_button'));
//		$output .= html_writer::link($CFG->wwwroot.'/local/capdmcontactus/view.php?id=-1&action=3&cid='.$order_id.'&step=1', get_string('checkout','local_capdmcontactus'), array('title'=>get_string('gotocheckout','local_capdmcontactus')));
//		$output .= html_writer::end_tag('div');


//		$form_checkout_1 = new local_capdmcontactus_checkout_coupon_form('view.php?id=-1', array('action'=>3, 'orderid'=>$order_id), 'post', '', array('class'=>'capdmcontactus_step_form'));
//		$outputform = $form_checkout_1->display();

		// End order holder
		$output .= html_writer::end_tag('div');


		$output .= '<script type="text/javascript">
						YUI().use("node", function(Y) {
							Y.all(".capdmcontactus_step_form").removeClass("mform");
						});
					</script>';
		return $output;
		
	
	}

	/**
	*	This function provides the checkout steps
	*
	*	@param $user_id = userid of current user - additional security check
	*   @param $cid     = order id
	*	@return string
	*/
	function  checkout($user_id, $cid, $step){
	
		global $DB, $USER;
		
		
		$output = '';
		
		// Start order holder
		$output .= html_writer::start_tag('div', array('id'=>'capdmcontactus_order_holder'));
		
		$output .= html_writer::start_tag('div', array('id'=>'capdmcontactus_checkout_summary'));
		$output .= html_writer::tag('h1', get_string('capdmcontactus_checkout_step_title','local_capdmcontactus').$step);
		$output .= html_writer::tag('p', get_string('capdmcontactus_checkout_step_'.$step,'local_capdmcontactus'));
		$output .= html_writer::end_tag('div');
				
		// end order holder
		$output .= html_writer::end_tag('div');
		
		return $output;
		
	}

	/**
	*	This function provides some error messages
	*
	*	@param $err = id of the error message to display
	*	@return string
	*/
	function  capdmcontactus_error($err){
	
		$output = '';
	
		$output .= html_writer::start_tag('div', array('id'=>'infomessage', 'class'=>'highlight'));

		switch($err){
			case 'bad_coupon': // bad or missing coupon
				$output .= html_writer::tag('p', get_string('badormissingcoupon','local_capdmcontactus'));
				break;
			case 'good_coupon': // coupon was valid
				$output .= html_writer::tag('p', get_string('goodcoupon', 'local_capdmcontactus'));
				break;
			case 'used_coupon': // coupon has been used
				$output .= html_writer::tag('p', get_string('usedcoupon', 'local_capdmcontactus'));
				break;
			case 'bad_order_details':  // bad order details have been passed
				$output .= html_writer::tag('p', get_string('error_order_details', 'local_capdmcontactus'));
				break;
			case 'already_in_order':  // item is already in the order
				$output .= html_writer::tag('p', get_string('already_in_order', 'local_capdmcontactus'));
				break;
			default: // catch all
				$output .= html_writer::tag('p', get_string('unhandlederror','local_capdmcontactus'));
				break;
		}
		
		$output .= html_writer::end_tag('div');
		
		return $output;

	}


// close class
}
