<?php
namespace SmarthomeStudio\admin\metaboxes;

use SmarthomeStudio\admin\fieldsManager as FieldManager;

defined( 'ABSPATH' ) || exit;

class Smarthome_Studio_Metaboxes {

	/**
	 * Version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';


	/**
	 * The single instance of the class.
	 *
	 * @var Smarthome_Studio_Metaboxes
	 * @since 1.0
	 */
	private static $_instance;

	/**
	 * Main Smarthome_Studio_Metaboxes Instance.
	 *
	 * Ensures only one instance of Smarthome_Studio_Metaboxes is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return Smarthome_Studio_Metaboxes - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}



	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'smarthome-studio' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'smarthome-studio' ), '1.0' );
	}


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_metabox_data' ) );
	}


	/**
	 * Meta Box In btf_builder post type.
	 */
	public function metaboxes() {
		add_meta_box( 'jws_metaboxes_setting', 'Template Settings', array( $this, 'jws_metaboxes_output' ), 'jws_builder', 'normal', 'high' );
	}


	/**
	 * Render Meta field.
	 *
	 * @param  POST $post Currennt post object which is being displayed.
	 */
	function jws_metaboxes_output( $post ) {
		$values            = get_post_custom( $post->ID );
		$template_type     = isset( $values['jws_template_type'] ) ? esc_attr( $values['jws_template_type'][0] ) : '';
		$block_hook     = isset( $values['jws_block_hook'] ) ? esc_attr( $values['jws_block_hook'][0] ) : '';


		// We'll use this nonce field later on when saving.
		wp_nonce_field( 'jws_meta_nounce', 'jws_meta_nounce' );
		?>
		<table class="smarthome-custom-wpadmin-table smarthome-jws-options-table jws-options-none">
			<tbody>
				<tr>
					<th scope="row">
						<label for="select_id"><?php _e( 'Template Type', 'smarthome-studio' ); ?></label>
					</th>
					<td>
						<div class="smarthome-custom-wpadmin-form-row">
							<select name="jws_template_type" id="jws_template_type">
								<option value="" <?php selected( $template_type, '' ); ?>><?php _e( 'Select Option', 'smarthome-studio' ); ?></option>
								
								<option value="tmp_header" <?php selected( $template_type, 'tmp_header' ); ?>><?php _e( 'Header', 'smarthome-studio' ); ?></option>

								<option value="tmp_footer" <?php selected( $template_type, 'tmp_footer' ); ?>><?php _e( 'Footer', 'smarthome-studio' ); ?></option>

								<option value="single-room" <?php selected( $template_type, 'single-room' ); ?>><?php _e( 'Single Room', 'smarthome-studio' ); ?></option>

								<option value="single-post" <?php selected( $template_type, 'single-post' ); ?>><?php _e( 'Single Post', 'smarthome-studio' ); ?></option>

								<!-- <option value="listing-archive" <?php selected( $template_type, 'listing-archive' ); ?>><?php _e( 'Listing Archive', 'smarthome-studio' ); ?></option> -->

								<option value="tmp_megamenu" <?php selected( $template_type, 'tmp_megamenu' ); ?>><?php _e( 'Mega Menu', 'smarthome-studio' ); ?></option>

								<option value="tmp_custom_block" <?php selected( $template_type, 'tmp_custom_block' ); ?>><?php _e( 'Block', 'smarthome-studio' ); ?></option>
						
                                <option value="tmp_slider" <?php selected( $template_type, 'tmp_slider' ); ?>><?php _e( 'Slider', 'smarthome-studio' ); ?></option>
                               
                                <option value="tmp_title_bar" <?php selected( $template_type, 'tmp_title_bar' ); ?>><?php _e( 'Title Bar', 'smarthome-studio' ); ?></option>
                                
                        
                        	</select>
						</div><!-- smarthome-custom-wpadmin-form-row -->
					</td>
				</tr>

				<tr class="jws-row jws-block-hook">
					<th scope="row">
						<label for="jws_block_hooks">
							<?php esc_html_e( 'Block Hook', 'smarthome-studio' ); ?>
						</label>
					</th>
					<td>
						<select name="jws_block_hooks" id="jws_block_hooks" class="form-control">
							<option value=""><?php esc_html_e( 'Select a Hook', 'smarthome-studio' );?></option>
							<option <?php selected( $block_hook, 'shortcode' ); ?> value="shortcode"><?php esc_html_e( 'Custom (Shortcode)', 'smarthome-studio' );?></option>
							<option <?php selected( $block_hook, 'before_header' ); ?> value="before_header"><?php esc_html_e( 'Before Header', 'smarthome-studio' );?></option>
							<option <?php selected( $block_hook, 'after_header' ); ?> value="after_header"><?php esc_html_e( 'After Header', 'smarthome-studio' );?></option>
							<option <?php selected( $block_hook, 'before_footer' ); ?> value="before_footer"><?php esc_html_e( 'Before Footer', 'smarthome-studio' );?></option>
							<option <?php selected( $block_hook, 'after_footer' ); ?> value="after_footer"><?php esc_html_e( 'After Footer', 'smarthome-studio' );?></option>
						</select>
					</td>
				</tr>
				
				<?php $this->display_rules_tab(); ?>

				<tr class="jws-row jws-shortcode-row">
					<th scope="row">
						<label for="jws_shortcode">
							<?php esc_html_e( 'Shortcode', 'smarthome-studio' ); ?>
						</label>
					</th>
					<td>
						<span class="jws-shortcode-wrap">
							<input type="text" onfocus="this.select();" readonly="readonly" value="[jws_template id='<?php echo esc_attr( $post->ID ); ?>']" class="code">
						</span>
					</td>
				</tr>

			</tbody>
		</table>
		<?php
	}

	/**
	 * Markup for Display Rules Tabs.
	 *
	 * @since  1.0.0
	 */
	public function display_rules_tab() {
	
		$included_settings = get_post_meta( get_the_id(), 'jws_included_options', true );
		$excluded_settings = get_post_meta( get_the_id(), 'jws_excluded_options', true );
		?>
		<tr class="jws-row jws-row-rules">
			<th scope="row">
				<label for="jws_included_display_rules">
					<?php esc_html_e( 'Display Location', 'smarthome-studio' ); ?>
				</label>
			</th>
			<td>
				<?php
				FieldManager\Jwsthemes_Field_Manager::jws_FieldSettings(
					'jws_included_display_rules',
					[
						'rule_type'      => 'display',
						'button_label' => __( 'Create Display Rule', 'smarthome-studio' ),
					],
					$included_settings
				);
				?>
			</td>
		</tr>

		<tr class="jws-row jws-row-rules-exclude hidden">
			<th scope="row">
				<label for="jws_included_display_rules">
					<?php esc_html_e( 'Exlcude Location', 'smarthome-studio' ); ?>
				</label>
			</th>
			<td>
				<?php
				FieldManager\Jwsthemes_Field_Manager::jws_FieldSettings(
					'jws_excluded_display_rules',
					[
						'rule_type'      => 'exclude',
						'button_label' => __( 'Create Exlcude Rule', 'smarthome-studio' ),
					],
					$excluded_settings
				);
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save metabox data for the current post.
	 *
	 * @param int $postId Current post ID.
	 */
	public function save_metabox_data($postId) {
	    if ($this->should_not_save_metabox($postId)) {
	        return;
	    }

	    $this->update_template_rules_metadata($postId, $_POST);
	    $this->update_template_type_metadata($postId, $_POST);
	    $this->update_block_hook_metadata($postId, $_POST);
	}

	/**
	 * Determine if metabox data should not be saved.
	 *
	 * @param int $postId Current post ID.
	 * @return bool True if data should not be saved, false otherwise.
	 */
	private function should_not_save_metabox($postId) {
	    return defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
	        || !isset($_POST['jws_meta_nounce'])
	        || !wp_verify_nonce(sanitize_text_field($_POST['jws_meta_nounce']), 'jws_meta_nounce')
	        || !current_user_can('edit_post', $postId);
	}

	/**
	 * Update target location metadata.
	 *
	 * @param int   $postId Current post ID.
	 * @param array $postData POST data.
	 */
	private function update_template_rules_metadata($postId, $postData) {
	    $targetLocations = FieldManager\Jwsthemes_Field_Manager::format_rule_metadata($postData, 'jws_included_display_rules');
	    $targetExclusion = FieldManager\Jwsthemes_Field_Manager::format_rule_metadata($postData, 'jws_excluded_display_rules');
	    
	    update_post_meta($postId, 'jws_included_options', $targetLocations);
	    update_post_meta($postId, 'jws_excluded_options', $targetExclusion);
	}

	/**
	 * Update template type metadata.
	 *
	 * @param int   $postId Current post ID.
	 * @param array $postData POST data.
	 */
	private function update_template_type_metadata($postId, $postData) {
	    if (isset($postData['jws_template_type'])) {
	    	$jws_template_type = sanitize_text_field($postData['jws_template_type']);
	        update_post_meta($postId, 'jws_template_type', $jws_template_type);
	       $jws_template_type = $this->get_single_tax_type($jws_template_type);
	         wp_set_object_terms( $postId, $jws_template_type, 'jws_types' );
      
	    }
	}

	private function get_single_tax_type($jws_template_type) {
	    
	    if( $jws_template_type == 'single-listing' ||
	    	$jws_template_type == 'single-room' ||
	    	$jws_template_type == 'single-agent' ||
	    	$jws_template_type == 'single-agency' ||
	    	$jws_template_type == 'single-post'
	    ) {
	    	return 'tmp_single';
	    }else {
	       return $jws_template_type;
	    }

	}

	/**
	 * Update block hook metadata.
	 *
	 * @param int   $postId Current post ID.
	 * @param array $postData POST data.
	 */
	private function update_block_hook_metadata($postId, $postData) {
	    if (isset($postData['jws_block_hooks'])) {
	    	$jws_block_hooks = sanitize_text_field($postData['jws_block_hooks']);
	        update_post_meta($postId, 'jws_block_hook', $jws_block_hooks);
	    }
	}

}
Smarthome_Studio_Metaboxes::instance();