<?php

/**
 * 
 */


add_action( 'widgets_init', create_function( '', 'register_widget( "dntly_donation_form_sidebar" );' ) );

class Dntly_Donation_Form_Sidebar extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'dntly_donate_widget_sidebar', // Base ID
			'Donately: Sidebar Donation Form', // Name
			array( 'description' => __( 'Default Size: 300x558px', 'dntly' ), ) // Args
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
		extract( $instance );
		require_once( DNTLY_PLUGIN_PATH . '/lib/donately-formjs.php');
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
		return $new_instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'campaign' ] ) ) { $campaign = $instance[ 'campaign' ]; }else{ $campaign = 0; }
		if ( isset( $instance[ 'css_url' ] ) ) { $css_url = $instance[ 'css_url' ]; }else{ $css_url = null; }
		$address = isset( $instance['address'] ) ? (bool) $instance['address'] : false;
		$phone = isset( $instance['phone'] ) ? (bool) $instance['phone'] : false;
		$iframe_height = isset( $instance['iframe_height'] ) ? $instance['iframe_height'] : null;
		$iframe_width = isset( $instance['iframe_width'] ) ? $instance['iframe_width'] : null;
		$comment = isset( $instance['comment'] ) ? (bool) $instance['comment'] : false;

		$dntly = new DNTLY_API;
		$account = $dntly->dntly_options['account'] . '.dntly.com';

		$campaigns = new WP_Query(
			array(
			'posts_per_page'	=> 150,
			'post_type'		    => $dntly->dntly_options['dntly_campaign_posttype'],
			'post_status'     => array( 'publish' ),
			'meta_query'      => array(
				array(
					'key'         => '_dntly_environment',
					'value'       => $dntly->dntly_options['environment'],
				)		
			))
		);
		$dntly_campaign_options = '';
		foreach( $campaigns->posts as $c ){
			$dntly_id  = get_post_meta($c->ID, '_dntly_id', true);
			$dntly_campaign_options .=	"<option value='{$dntly_id}' ".selected( $campaign, $dntly_id, false ).">{$c->post_title}</option>";
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'account' ); ?>"><?php _e( 'Donately Account:' ); ?></label> 
			<input class="widefat" type="text" value="<?php echo esc_attr( $account ); ?>" readonly />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'campaign' ); ?>"><?php _e( 'Donately Campaign:' ); ?></label> 
			<select name="<?php echo $this->get_field_name( 'campaign' ); ?>" style="width:220px">
				<option value="0">General (No Campaign)</option>
				<?php print $dntly_campaign_options ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'css_url' ); ?>"><?php _e( 'Custom CSS URL:' ); ?></label> 
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'css_url' ); ?>" value="<?php echo esc_attr( $css_url ); ?>" />
		</p>
		<p style="display:block;clear:both">
			<div style="width:100px;float:left">
				<label for="<?php echo $this->get_field_id( 'iframe_width' ); ?>"><?php _e( 'Custom Width:' ); ?></label> 
				<input type="text" name="<?php echo $this->get_field_name( 'iframe_width' ); ?>" value="<?php echo esc_attr( $iframe_width ); ?>" style="width:100px" />
			</div>
			<div style="width:100px;float:right">
				<label for="<?php echo $this->get_field_id( 'iframe_height' ); ?>"><?php _e( 'Custom Height:' ); ?></label> 
				<input type="text" name="<?php echo $this->get_field_name( 'iframe_height' ); ?>" value="<?php echo esc_attr( $iframe_height ); ?>" style="width:100px"/>
			</div>
		</p>		
		<p style="display:block;clear:both;padding-top:10px">
			<input class="checkbox" type="checkbox" <?php checked( $address ); ?> id="<?php echo $this->get_field_id( 'address' ); ?>" name="<?php echo $this->get_field_name( 'address' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'address' ); ?>"><?php _e( 'Display Address Fields?' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $phone ); ?> id="<?php echo $this->get_field_id( 'phone' ); ?>" name="<?php echo $this->get_field_name( 'phone' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'phone' ); ?>"><?php _e( 'Display Phone Field?' ); ?></label>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $comments ); ?> id="<?php echo $this->get_field_id( 'comment' ); ?>" name="<?php echo $this->get_field_name( 'comment' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'comment' ); ?>"><?php _e( 'Display Comments Field?' ); ?></label>
		</p>
		<?php 
	}

} // class Donately_Widgets