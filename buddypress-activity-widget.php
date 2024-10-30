<?php
/*
Plugin Name: WebAvenue Activity Widget
Plugin URI: http://webavenue.com.au
Description: Buddypress Activity Widget is a sidbar widget to show list of sitewide, members and member's friends activity on a sidebar. You can filter activities by scope, object and actions easily using this plugin.
Version: 1.0
Author: Rameshwor Maharjan
Author URI: http://webavenue.com.au

Copyright 2013 WebAvenue(http://webavenue.com.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( !class_exists( 'WaActivityWidget' ) ) {
/**
 * Main WebAvenue Acitivity Widget Class
 *
 *
 * @since WaActivityWidget (1.0)
 */
	class WaActivityWidget {
		
			var $plugin_id = 'wabpactivitywidget';
			
			
			public function approve_wp_user() {
				$this->__construct();
			}
			
			public function __construct() {
				register_activation_hook( __FILE__, array( $this, 'activation_check' ) );
				add_action( 'init', array( $this, 'init' ) );
				add_action( 'widgets_init', array( $this, 'bp_activity_widget' ) );  
			}
			
			/* check the wordpress version compatibility */
			public function activation_check() {
				
				global $wp_version;	
				$min_wp_version = '3.2.1';
				$exit_msg = sprintf( __( 'Activity Widget requires WordPress %s or newer.', $this->plugin_id ), $min_wp_version );
				if ( version_compare( $wp_version, $min_wp_version, '<=' ) ) {
					exit( $exit_msg );
				}
				
				if ( !class_exists ( 'buddypress' ) ) {
					exit( "Please install BuddyPress first!!" );
				}
	
			}
			
			/* load any javascript and css needed for the plugin */
			public function init() {
				wp_enqueue_style( 'wabpactivitywidget_css', plugins_url( '/css/activity-widget-style.css', __FILE__ ) );
			}
			
			public function bp_activity_widget() {  
				register_widget( 'WebAvenue_Activity_Widget' );  
			} 
			
			
		
		
	}
	
	 // initialize class
	
	if ( class_exists( 'WaActivityWidget' ) ) {
		$WaActivityWidget = new WaActivityWidget();
	}

}


class WebAvenue_Activity_Widget extends WP_Widget {
	
	var $scopes = array( "just-me", "friends", "groups", "favorites", "mentions" );

	function WebAvenue_Activity_Widget() {
		$widget_ops = array( 'classname' => 'wa-bp-activity-widget', 'description' => __('A widget that displays the buddypress activity ', $this->plugin_id ) );
		
		$control_ops = array( 'width' => 200, 'height' => 350, 'id_base' => 'wa-bp-activity-widget' );
		
		$this->WP_Widget( 'wa-bp-activity-widget', __( 'BuddyPress Activity Widget', $this->plugin_id ), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters( 'widget_title', $instance[ 'title' ] );
		$max_num = (int)$instance[ 'max-num' ];
		$activity_scope = $instance[ 'wa-activity-scope' ];
		$activity_object = $instance[ 'wa-activity-object' ];
		$activity_actions = $instance[ 'wa-activity-actions' ];
		$activity_custom_actions = $instance[ 'activity-custom-actions' ];
		$show_info = isset( $instance['show-info'] ) ? $instance['show-info'] : false;
		
		
		echo $before_widget;

		// Display the widget title 
		if ( $title )
			echo $before_title . $title . $after_title;
			
		
		$show_widget = true;
		
		if( $show_info && !is_user_logged_in() ){
			
			$show_widget = false;
		}
		if( $show_widget ) {	
			if( is_array( $activity_object ) )
			$default_object = implode( "," , $activity_object );
			else
			$default_object = 'activity,blogs,friends,groups,profile,xprofile';
		
			if( is_array( $activity_scope ) )
			$default_scope = implode( "," , $activity_scope );
			else
			$default_scope = '';
		
			if( is_array( $activity_actions ) ) {
				$default_action = implode( "," , $activity_actions );
				if( !empty( $activity_custom_actions ) )
					$default_action .=  $activity_custom_actions;
			}
			else
			$default_action = '';
		
			
?>
		<ul id="wa-bp-activity-stream" class="wa-bp-activity-list item-list">
       
				<?php
                      if ( bp_has_activities( 'type=sitewide&max='.$max_num.'&object='.$default_object.'&scope='.$default_scope.'&action='.$default_action.'&user_id=&primary_id=' ) ) {
                            while ( bp_activities() ) { bp_the_activity();
                                
                ?>   
                       <li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">
                        <span class="wa-bp-activity-avatar">
                            <a href="<?php bp_activity_user_link(); ?>">
                                <?php bp_activity_avatar(); ?>
                            </a>
                        </span>
                
                        <span class="wa-bp-activity-content">
                            <span class="wa-bp-activity-header"><object><?php bp_activity_action(); ?></object></span>
                            <?php if ( bp_activity_has_content() ) : ?>
                                <span class="wa-activity-inner"><object><?php bp_activity_content_body(); ?></object></span>
                            <?php endif; ?>
                        </span>
                </li>

<?php } }
?>
		</ul>
<?php
		

			
		} else {
			echo "Please login to see the activity stream.";
		}
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['max-num'] = (int)$new_instance['max-num'];
		$instance['wa-activity-scope'] =  $new_instance['wa-activity-scope'];
		$instance['wa-activity-object'] =  $new_instance['wa-activity-object'];
		$instance['wa-activity-actions'] =  $new_instance['wa-activity-actions'];
		$instance['activity-custom-actions'] = strip_tags( $new_instance['activity-custom-actions'] );
		$instance['show-info'] = isset( $new_instance['show-info'] ) ? 1 : 0;
		
		return $instance;
	}

	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __('Activity', $this->plugin_id), 
						   'show-info' => 1,
						   'max-num' => 10, 
						   'wa-activity-scope' => __('Everything', $this->plugin_id),
						   'wa-activity-object' => __('Everything', $this->plugin_id),
						   'wa-activity-actions' => __('Everything', $this->plugin_id) );
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
       
		
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Widget Title:', $this->plugin_id); ?></label></p>
        <p><input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:94%;" /></p>
    
        <p><label for="<?php echo $this->get_field_id( 'max-num' ); ?>"><?php _e('Show Only:', $this->plugin_id); ?></label></p>
		<p><input id="<?php echo $this->get_field_id( 'max-num' ); ?>" name="<?php echo $this->get_field_name( 'max-num' ); ?>" value="<?php echo $instance['max-num']; ?>" style="width:94%;" /></p>
		<p><label for="<?php echo $this->get_field_name( 'wa-activity-scope' ); ?>"><?php _e( 'Acitivity Scope:', $this->plugin_id ); ?></label>
        	<input type="button" name="scope_none" value="Clear" class="clear_filter" />
        </p>
		<p><select name="<?php echo $this->get_field_name( 'wa-activity-scope' ); ?>[]" id="wa-activity-scope" class="select-wa-activity" multiple="multiple" style="width:220px;height:130px;">
				<?php
				foreach( $this->scopes as $scope ):
				?>
                <option <?php if( is_array( $instance[ 'wa-activity-scope' ] ) && in_array( $scope, $instance[ 'wa-activity-scope' ] ) ) echo "selected='selected'";?>  value="<?php echo $scope; ?>"><?php _e( $scope, $this->plugin_id ); ?></option>
                <?php endforeach; ?>
            </select>
				
        </p>
       
        <p><label for="<?php echo $this->get_field_name( 'wa-activity-object' ); ?>"><?php _e( 'Acitivity Object:', $this->plugin_id ); ?></label>
        <input type="button" name="object_none" value="Clear" class="clear_filter" />
        </p>
		<p><select name="<?php echo $this->get_field_name( 'wa-activity-object' ); ?>[]" id="wa-activity-object" class="select-wa-activity" multiple="multiple" style="width:220px;height:130px;">
				<?php $components = BP_Activity_Activity::get_recorded_components();
				foreach( $components as $component ):
				?>
                <option <?php if( is_array( $instance[ 'wa-activity-object' ] ) && in_array( $component, $instance[ 'wa-activity-object' ] ) ) echo "selected='selected'";?> value="<?php echo $component; ?>"><?php _e( $component, $this->plugin_id ); ?></option>
                <?php endforeach; ?>
            </select>	
        </p>
        <p><label for="<?php echo $this->get_field_name( 'wa-activity-actions' ); ?>"><?php _e( 'Activity Actions:', $this->plugin_id ); ?></label>
         <input type="button" name="actions_none" value="Clear" class="clear_filter" />
        </p> 
        <p><select name="<?php echo $this->get_field_name( 'wa-activity-actions' ); ?>[]" id="wa-activity-actions" class="select-wa-activity" multiple="multiple" style="width:220px;height:130px;">
				<option <?php if( is_array( $instance[ 'wa-activity-actions' ] ) && in_array( $component, $instance[ 'wa-activity-actions' ] ) ) echo "selected='selected'";?> value="activity_update"><?php _e( 'Activity Updates', $this->plugin_id ); ?></option>

				<?php
				if ( !bp_is_current_action( 'groups' ) ) :
					if ( bp_is_active( 'blogs' ) ) : ?>

						<option <?php if( is_array( $instance[ 'wa-activity-actions' ] ) && in_array( "new_blog_post", $instance[ 'wa-activity-actions' ] ) ) echo "selected='selected'";?> value="new_blog_post"><?php _e( 'New Blog Posts', $this->plugin_id ); ?></option>
						<option <?php if( is_array( $instance[ 'wa-activity-actions' ] ) && in_array( "new_blog_comment", $instance[ 'wa-activity-actions' ] ) ) echo "selected='selected'";?> value="new_blog_comment"><?php _e( 'New Blog Comments', $this->plugin_id ); ?></option>

					<?php
					endif;

					if ( bp_is_active( 'friends' ) ) : ?>

						<option <?php if( is_array( $instance[ 'wa-activity-actions' ] ) && in_array( "friendship_accepted,friendship_created", $instance[ 'wa-activity-actions' ] ) ) echo "selected='selected'";?> value="friendship_accepted,friendship_created"><?php _e( 'Friendships', $this->plugin_id ); ?></option>

					<?php endif;

				endif;

				if ( bp_is_active( 'forums' ) ) : ?>

					<option <?php if( is_array( $instance[ 'wa-activity-actions' ] ) && in_array( "new_forum_topic", $instance[ 'wa-activity-actions' ] ) ) echo "selected='selected'";?> value="new_forum_topic"><?php _e( 'Forum Topics', $this->plugin_id ); ?></option>
					<option <?php if( is_array( $instance[ 'wa-activity-actions' ] ) && in_array( "new_forum_post", $instance[ 'wa-activity-actions' ] ) ) echo "selected='selected'";?> value="new_forum_post"><?php _e( 'Forum Replies', $this->plugin_id ); ?></option>

				<?php endif;

				if ( bp_is_active( 'groups' ) ) : ?>

					<option <?php if( is_array( $instance[ 'wa-activity-actions' ] ) && in_array( "created_group", $instance[ 'wa-activity-actions' ] ) ) echo "selected='selected'";?> value="created_group"><?php _e( 'New Created Groups', $this->plugin_id ); ?></option>
					<option <?php if( is_array( $instance[ 'wa-activity-actions' ] ) && in_array( "joined_group", $instance[ 'wa-activity-actions' ] ) ) echo "selected='selected'";?> value="joined_group"><?php _e( 'Group Memberships', $this->plugin_id ); ?></option>

				<?php endif; ?>

			

			</select>
        </p>
        <p><label for="activity-custom-actions"><?php _e( 'Custom Activity Actions:', $this->plugin_id ); ?></label></p>
        <p><input id="<?php echo $this->get_field_id( 'activity-custom-actions' ); ?>" name="<?php echo $this->get_field_name( 'activity-custom-actions' ); ?>" value="<?php echo $instance['activity-custom-actions']; ?>" style="width:94%;" /> Seperate by comma for multiple custom actions.</p>
		<p><label for="<?php echo $this->get_field_id( 'show-info' ); ?>"><?php _e('Show only to logged in user?', $this->plugin_id ); ?></label>
        &nbsp;<input class="checkbox" type="checkbox" <?php echo( $instance['show-info'] == 1 ? "checked = 'checked' " : "" ); ?> id="<?php echo $this->get_field_id( 'show-info' ); ?>" name="<?php echo $this->get_field_name( 'show-info' ); ?>" value="info" /></p>
		 <script>
        jQuery( document ).ready( function() {
        
            jQuery(".clear_filter").on("click", function(){
                var index = jQuery(this).index(".clear_filter");
                jQuery(".select-wa-activity").eq(index).find("option").removeAttr("selected");
            });
        
        });
		</script>
		
		

	<?php
	}
}

?>