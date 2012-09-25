<?php
/*
Plugin Name: WriteToThem Widget
Plugin URI: http://philipjohn.co.uk/category/plugins/writetothem/
Description: A widget which provides an entry point for mySociety's WriteToThem.com, now completely re-written
Author: Philip John
Version: 2.1
Author URI: http://philipjohn.co.uk
License: GPLv3
License URI: license.txt
Text Domain: writetothem
Domain Path: /languages
*/
/*  Copyright 2012  Philip John Ltd  (email : talkto@philipjohn.co.uk)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Initial sanity check
if (! defined('ABSPATH'))
	die('Please do not directly access this file');

// Localise
load_plugin_textdomain('writetothem');

Class WriteToThem extends WP_Widget {
	
	/**
	 * Register the widget
	 */
	public function __construct() {
		parent::__construct(
	 		'writetothem_widget', // Base ID
			'WriteToThem.com', // Name
			array( 'description' => __( 'Make it easy for your visitors to contact their local politicians with this widget', 'writetothem' ), ) // Args
		);
	}
	
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$pre_text = apply_filters('wtt_pre_text', $instance['pre_text']);
		
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		if ($pre_text!=""){echo "<p>$pre_text</p>";}
	    ?>
	    <form method="get" action="http://www.writetothem.com/">
	        <input type="hidden" name="a" value="<?php echo $instance['contact_type']; ?>">
	            <div style="<?php echo apply_filters('wtt_div_style', 'margin-bottom:0.5em;'); ?>"><label for="text"><?php _e('Enter your postcode', 'writetothem'); ?></label></div>
	            <div><input type="text" name="pc" size="<?php echo apply_filters('wtt_input_length', "13"); ?>">
	            <input type="submit" value="<?php _e('Go', 'writetothem'); ?>"></div>
	    </form>
	    <?php
		echo $after_widget;
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['pre_text'] = strip_tags( $new_instance['pre_text'] );
		
		// Check the content type is a valid one, then make sure if ALL, we reset to ''
		$instance['contact_type'] = (self::is_allowed_contact_type($new_instance['contact_type']))? $new_instance['contact_type'] : '';
		$instance['contact_type'] = ($new_instance['contact_type']=='all')? '' : $new_instance['contact_type'];
		
		return $instance;
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$defaults = self::default_widget();
		$title = ( isset( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : $default['title'];
		$pre_text = ( isset( $instance[ 'pre_text' ] ) ) ? $instance[ 'pre_text' ] : $default['pre_text'];
		$contact_type = ( isset( $instance[ 'contact_type' ] ) ) ? $instance[ 'contact_type' ] : $default['contact_type'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'pre_text' ); ?>"><?php _e( 'Text to display: ', 'writetothem' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'pre_text' ); ?>" name="<?php echo $this->get_field_name( 'pre_text' ); ?>" type="text" value="<?php echo esc_attr( $pre_text ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'contact_type' ); ?>"><?php _e('Who would you like people to contact?: ', 'writetothem'); ?></label>
	        <select id="<?php echo $this->get_field_id( 'contact_type' ); ?>" name="<?php echo $this->get_field_name( 'contact_type' ); ?>">
	            <option value="all" <?php if($contact_type == '') { echo 'selected="selected"'; } ?>><?php _e('The lot of them', 'writetothem'); ?></option>
	            <option value="westminstermp" <?php if($contact_type == 'westminstermp') { echo 'selected="selected"'; } ?>><?php _e('Westminster MP', 'writetothem'); ?></option>
	            <option value="council" <?php if($contact_type == 'council') { echo 'selected="selected"'; } ?>><?php _e('Councillors', 'writetothem'); ?></option>
	            <option value="regionalmp" <?php if($contact_type == 'regionalmp') { echo 'selected="selected"'; } ?>><?php _e('Regional MP', 'writetothem'); ?></option>
	            <option value="mep" <?php if($contact_type == 'mep') { echo 'selected="selected"'; } ?>><?php _e('Members of European Parliament', 'writetothem'); ?></option>
	        </select>
		</p>
		<?php 
	}
	
	/**
	 * Check the contact type is valid
	 * 
	 * @param string $type The type chosen by the user
	 * 
	 * @return bool True = valid, False = invalid
	 */
	private function is_allowed_contact_type($type){
		$allowed = array(
			'all',
			'westminstermp',
			'council',
			'regionalmp',
			'mep'
		);
		$found = array_search($type, $allowed);
		return ($found===true)? true : false;
	}
	
	/**
	 * Set default widget options
	 */
	private function default_widget(){
		return array(
			'title' => __( 'WriteToThem.com', 'writetothem' ),
			'pre_text' => __( 'Write to your Councillors, MP, MEPs, MSPs, or Welsh and London Assembly Member by entering your postcode below and clicking Go', 'writetothem' ),
			'contact_type' => ''
		);
	}
	
	/**
	 * Debug logging
	 */
	private function log($data){
		$path = trailingslashit(ABSPATH);
		if (is_array($data)){
			$msg = "Array (\r\n";
			foreach ($data as $key => $value){
				$msg .= "	$key => $value\r\n";
			}
			$msg .= ")";
		}
		else if (is_object($data)){
			$objectvars = get_object_vars($data);
			$msg = "Object (\r\n	Vars (\r\n";
			foreach ($objectvars as $name => $value){
				$msg .= "		$name => $value\r\n";
			}
			$msg .= "	)\r\n";
			
			$objectmeths = get_object_vars($data);
			$msg .= "	Methods (\r\n";
			foreach ($objectmeths as $name){
				$msg .= "		$name\r\n";
			}
			$msg .= "	)\r\n)";
		}
		else {
			$msg = $data;
		}
		
		$now = time();
		file_put_contents($path.'writetothem.log', "$now: ".$msg."\r\n", FILE_APPEND);
	}
}
add_action( 'widgets_init', create_function( '', 'register_widget( "WriteToThem" );' ) );

?>