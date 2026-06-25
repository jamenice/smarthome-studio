<?php
namespace SmarthomeStudio\admin\post_type;

use SmarthomeStudio\admin\fieldsManager as FieldManager;

defined( 'ABSPATH' ) || exit;

class Smarthome_Studio_Post_Type {

	/**
	 * Version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';


	/**
	 * The single instance of the class.
	 *
	 * @var Smarthome_Studio_Post_Type
	 * @since 1.0
	 */
	private static $_instance;

	/**
	 * Main Smarthome_Studio_Post_Type Instance.
	 *
	 * Ensures only one instance of Smarthome_Studio_Post_Type is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return Smarthome_Studio_Post_Type - Main instance. 
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

		$this->cpt_support();

		add_action( 'init', array( $this, 'post_type' ) );
		
		if ( is_admin() ) {
			add_filter( 'manage_jws_builder_posts_columns', array( $this, 'columns_head' ) );
			add_action( 'manage_jws_builder_posts_custom_column', array( $this, 'columns_content' ), 10, 2 );
			add_filter( 'views_edit-jws_builder', [ $this, 'admin_print_tabs' ] );
		
			// Add drag & drop sortable with auto date update
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_sortable_scripts' ) );
            add_action( 'wp_ajax_jws_builder_update_order', array( $this, 'ajax_update_order' ) );
            
            // Make posts sortable by date (newest first)
            add_filter( 'pre_get_posts', array( $this, 'set_default_sort_order' ) );
		}

	}


	/**
     * Set default sort order by date (newest first)
     */
    public function set_default_sort_order( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( 'jws_builder' === $query->get( 'post_type' ) ) {
            // If no orderby is set, use date
            if ( ! isset( $_GET['orderby'] ) ) {
                $query->set( 'orderby', 'date' );
                $query->set( 'order', 'DESC' );
            }
        }
    }

    /**
     * Enqueue scripts for drag & drop sorting with auto date update
     */
     public function enqueue_sortable_scripts( $hook ) {
        if ( 'edit.php' !== $hook || ! isset( $_GET['post_type'] ) || 'jws_builder' !== $_GET['post_type'] ) {
            return;
        }

        // Enqueue jQuery and jQuery UI Sortable
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        
        // Add inline script that depends on jQuery
        wp_add_inline_script( 'jquery-ui-sortable', $this->get_sortable_script() );
        
        // Add inline styles
        wp_add_inline_style( 'wp-admin', $this->get_sortable_styles() );
    }


	/**
     * Get sortable styles
     */
    private function get_sortable_styles() {
        return '
            .ui-sortable-helper {
                background-color: #f0f0f1;
                opacity: 0.8;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .ui-sortable-placeholder {
                visibility: visible !important;
                background-color: #dcdcde;
                height: 50px;
            }
            #the-list tr {
                cursor: move;
            }
            #the-list tr:hover {
                background-color: #f6f7f7;
            }
            .jws-drag-notice {
                position: fixed;
                top: 32px;
                right: 20px;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            }
            @keyframes slideIn {
                from { transform: translateX(100%); }
                to { transform: translateX(0); }
            }
        ';
    }

    /**
     * Get sortable script
     */
   private function get_sortable_script() {
        $nonce = wp_create_nonce( 'jws_builder_sort_nonce' );
        
        return "
        jQuery(document).ready(function($) {
            // Store original positions and dates
            var originalData = [];
            
            $('#the-list tr').each(function(index) {
                var postId = $(this).attr('id');
                if (postId) {
                    postId = postId.replace('post-', '');
                    var dateText = $(this).find('.date').text().trim();
                    originalData.push({
                        id: postId,
                        position: index,
                        dateText: dateText
                    });
                }
            });

            $('#the-list').sortable({
                placeholder: 'ui-sortable-placeholder',
                helper: function(e, tr) {
                    var \$helper = tr.clone();
                    \$helper.children().each(function(index) {
                        $(this).width(tr.children().eq(index).width());
                    });
                    return \$helper;
                },
                start: function(event, ui) {
                    // Store the dragged item's original position
                    ui.item.data('oldIndex', ui.item.index());
                },
                update: function(event, ui) {
                    var order = [];
                    var draggedPostId = ui.item.attr('id');
                    if (!draggedPostId) return;
                    
                    draggedPostId = draggedPostId.replace('post-', '');
                    var newIndex = ui.item.index();
                    var oldIndex = ui.item.data('oldIndex');
                    
                    // Collect all posts in new order
                    $('#the-list tr').each(function(index) {
                        var postId = $(this).attr('id');
                        if (postId) {
                            postId = postId.replace('post-', '');
                            order.push({
                                id: postId,
                                position: index
                            });
                        }
                    });

                    // Determine if we need to update date and get reference post
                    var updateDate = false;
                    var referencePostId = null;
                    var direction = '';
                    
                    if (newIndex < oldIndex) {
                        // Moved up (to earlier position - newer date)
                        // Get the post that is now after the dragged post
                        if (order[newIndex + 1]) {
                            referencePostId = order[newIndex + 1].id;
                            updateDate = true;
                            direction = 'up';
                        }
                    } else if (newIndex > oldIndex) {
                        // Moved down (to later position - older date)
                        // Get the post that is now before the dragged post
                        if (order[newIndex - 1]) {
                            referencePostId = order[newIndex - 1].id;
                            updateDate = true;
                            direction = 'down';
                        }
                    }

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'jws_builder_update_order',
                            order: order,
                            dragged_post_id: draggedPostId,
                            reference_post_id: referencePostId,
                            update_date: updateDate,
                            direction: direction,
                            nonce: '{$nonce}'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show success message
                                var message = 'Order updated successfully!';
                                if (response.data && response.data.date_updated) {
                                    message = 'Order and date updated! New date: ' + response.data.new_date;
                                    
                                    // Update the date column in the UI
                                    $('#post-' + draggedPostId).find('.date').html(response.data.new_date_html);
                                }
                                
                                var notice = $('<div class=\"notice notice-success is-dismissible jws-drag-notice\"><p>' + message + '</p></div>');
                                $('body').append(notice);
                                
                                setTimeout(function() {
                                    notice.fadeOut(function() {
                                        $(this).remove();
                                    });
                                }, 3000);
                                
                                // Add dismiss button functionality
                                notice.find('.notice-dismiss').on('click', function() {
                                    notice.fadeOut(function() {
                                        $(this).remove();
                                    });
                                });
                            }
                        },
                        error: function() {
                            var notice = $('<div class=\"notice notice-error is-dismissible jws-drag-notice\"><p>Error updating order. Please try again.</p></div>');
                            $('body').append(notice);
                            
                            setTimeout(function() {
                                notice.fadeOut(function() {
                                    $(this).remove();
                                });
                            }, 3000);
                            
                            // Revert to original order on error
                            location.reload();
                        }
                    });
                }
            });
        });
        ";
    }
    /**
     * AJAX handler to update post order and auto-update dates
     */
    public function ajax_update_order() {
        check_ajax_referer( 'jws_builder_sort_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $order = isset( $_POST['order'] ) ? $_POST['order'] : array();
        $dragged_post_id = isset( $_POST['dragged_post_id'] ) ? absint( $_POST['dragged_post_id'] ) : 0;
        $reference_post_id = isset( $_POST['reference_post_id'] ) ? absint( $_POST['reference_post_id'] ) : 0;
        $update_date = isset( $_POST['update_date'] ) && $_POST['update_date'] === 'true';
        $direction = isset( $_POST['direction'] ) ? sanitize_text_field( $_POST['direction'] ) : '';

        $response_data = array();

        // Update menu_order for all posts
        foreach ( $order as $item ) {
            $post_id = absint( $item['id'] );
            $position = absint( $item['position'] );

            wp_update_post( array(
                'ID' => $post_id,
                'menu_order' => $position
            ), true, false ); // Skip firing hooks for performance
        }

        // If we need to update the date
        if ( $update_date && $reference_post_id && $dragged_post_id ) {
            $reference_post = get_post( $reference_post_id );
            $dragged_post = get_post( $dragged_post_id );
            
            if ( $reference_post && $dragged_post ) {
                $reference_timestamp = strtotime( $reference_post->post_date );
                $dragged_timestamp = strtotime( $dragged_post->post_date );
                
                $should_update = false;
                $new_timestamp = 0;
                
                if ( $direction === 'up' ) {
                    // Moved up: dragged post should be newer than reference post
                    if ( $dragged_timestamp < $reference_timestamp ) {
                        // Set new date to 1 second after the reference post
                        $new_timestamp = $reference_timestamp + 1;
                        $should_update = true;
                    }
                } elseif ( $direction === 'down' ) {
                    // Moved down: dragged post should be older than reference post
                    if ( $dragged_timestamp > $reference_timestamp ) {
                        // Set new date to 1 second before the reference post
                        $new_timestamp = $reference_timestamp - 1;
                        $should_update = true;
                    }
                }
                
                if ( $should_update ) {
                    $new_date = date( 'Y-m-d H:i:s', $new_timestamp );
                    $new_date_gmt = get_gmt_from_date( $new_date );

                    // Update post date
                    wp_update_post( array(
                        'ID' => $dragged_post_id,
                        'post_date' => $new_date,
                        'post_date_gmt' => $new_date_gmt,
                        'post_modified' => current_time( 'mysql' ),
                        'post_modified_gmt' => current_time( 'mysql', 1 ),
                    ) );

                    $response_data['date_updated'] = true;
                    $response_data['new_date'] = date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $new_timestamp );
                    $response_data['new_date_html'] = '<abbr title="' . date_i18n( 'Y/m/d g:i:s a', $new_timestamp ) . '">' . 
                                                       date_i18n( 'Y/m/d', $new_timestamp ) . '</abbr><br>' .
                                                       '<span class="published">Published</span>';
                }
            }
        }

        wp_send_json_success( $response_data );
    }
	/**
     * Custom post type
     *
     * @access public
     * @return void
     */
    public static function post_type() {
    	global $wp;

    	register_taxonomy(
            'jws_types',
            ['jws_builder'],
            [
                'hierarchical' => false,
                'public' => false,
                'label' => _x( 'Type', 'Theme Builder', 'smarthome-studio' ),
                'show_ui' => false,
                'show_admin_column' => false,
                'query_var' => true,
                'show_in_rest' => false,
                'rewrite' => false,
            ]
        );
    	

        $labels = array(
            'name' => __( 'Theme Builder','smarthome-studio'),
            'singular_name' => __( 'Theme Builder','smarthome-studio' ),
            'add_new_item' => __('Add New Layout','smarthome-studio'),
            'edit_item' => __('Edit Layout','smarthome-studio'),
            'all_items'     => esc_html__( 'All Layouts', 'smarthome-studio' ),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'can_export' => true,
            'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'taxonomies' => ['jws_types'],
            'menu_icon' => 'dashicons-editor-kitchensink',
            'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
            'show_in_rest'       => true,
            'rest_base'          => 'jws_studio',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rewrite'      => array(
				'slug'       => 'smarthome-studio',
				'with_front' => false,
				'feeds'      => true,
			),
        );

        register_post_type('jws_builder',$args);
    }

    /**
	 * Add post type support.
	 */
	public function cpt_support() {
		add_post_type_support( 'jws_builder', 'elementor' );
	}


	/**
	 * Add columns for custom post type
	 */
	public function columns_head($columns) {
	    $date_column = $columns['date'];
	    unset($columns['date']);
	    $columns['type'] = __('Type', 'smarthome-studio');
	    $columns['display_rules'] = __('Display Rules', 'smarthome-studio');
	    $columns['shortcode'] = __('Shortcode', 'smarthome-studio');
	    $columns['date'] = $date_column;
	    return $columns; 
	}

	/**
	 * Add columns content
	 */
	public function columns_content($column_name, $post_id) {
	    
	    switch ($column_name) {

	        case 'type':
	            $type = get_post_meta($post_id, 'jws_template_type', true);
	            $jws_hook = get_post_meta($post_id, 'jws_block_hook', true);
	            $this->outputColumnHTML("jws-template-type", esc_html($type));
	            $this->outputColumnHTML("jws-block-hook", esc_html($jws_hook));
	            break;

	        case 'display_rules':
	            $included = get_post_meta($post_id, 'jws_included_options', true);
	            if (!empty($included)) {
	                echo '<div class="jws-columns-content">';
	                echo '<strong>Display: </strong>';
	                $this->display_rules_markup($included);
	                echo '</div>';
	            }

	            $excluded = get_post_meta($post_id, 'jws_excluded_options', true);
	            if (!empty($excluded)) {
	                echo '<div class="jws-columns-content">';
	                echo '<strong>Exclusion: </strong>';
	                $this->display_rules_markup($excluded);
	                echo '</div>';
	            }
	            break;

	        case 'shortcode':
	            $this->outputColumnHTML("jws-shortcode-wrap", "[jws_template id='" . esc_attr($post_id) . "']", true);
	            break;
	    }
	}

	/**
	 * Generates and outputs HTML markup based on provided display rules for a specific column.
	 * This function iterates over display rules, fetches corresponding labels, and constructs
	 * a comma-separated list to be displayed in the admin column.
	 *
	 * @param array $display_rules An associative array containing the display rules.
	 */

	public function display_rules_markup($display_rules) {
	    $labels = [];

	    // Consolidate the logic for processing 'rule' and 'specific' elements.
	    $rule_types = ['rule', 'specific'];

	    foreach ($rule_types as $rule_type) {
	        if (isset($display_rules[$rule_type]) && is_array($display_rules[$rule_type])) {
	            // Remove 'specifics' from 'rule' type if it exists.
	            if ($rule_type === 'rule') {
	                $index = array_search('specifics', $display_rules[$rule_type]);
	                if ($index !== false) {
	                    unset($display_rules[$rule_type][$index]);
	                }
	            }

	            // Process each rule in the current type.
	            foreach ($display_rules[$rule_type] as $rule) {
	                $label = FieldManager\Jwsthemes_Field_Manager::derive_label_from_key($rule);
	                if ($label) {
	                    $labels[] = $label;
	                }
	            }
	        }
	    }

	    // Output the combined labels.
	    if (!empty($labels)) {
	        echo esc_html(join(', ', $labels));
	    }
	}

	private function outputColumnHTML($class, $text, $is_code = false) {
	    echo "<span class=\"" . esc_attr($class) . "\">";

	    if ($is_code) {
	        echo "<input type=\"text\" onfocus=\"this.select();\" readonly=\"readonly\" value=\"" . esc_attr($text) . "\" class=\"code\">";
	    } else {
	        // Use an associative array for cleaner code and easier updates
		        $textMappings = array(
		            'tmp_header'        => 'Header',
		            'tmp_footer'        => 'Footer',
		            'single-listing' => 'Single Listing',
		            'single-room'    => 'Single Room',
		            'single-agent' => 'Single Agent',
	            'single-agency' => 'Single Agency',
	            'single-post' => 'Single Post',
	            'before_header' => ' - Before Header',
	            'after_header'  => ' - After Header',
	            'before_footer' => ' - Before Footer',
	            'after_footer'  => ' - After Footer',
	            'tmp_megamenu'      => 'Mega Menu',
	            'tmp_custom_block'  => 'Block',
	        );

	        // Check if the text has a mapped value and update it accordingly
	        if (array_key_exists($text, $textMappings)) {
	            $text = $textMappings[$text];
	        }

	        echo esc_html($text);
	    }
	    echo "</span>";
	}


	/**
	 * Print Admin Tabs
	 *
	 * @param [type] $views
	 * @return void
	 * @since 1.1.0
	 */
	public function admin_print_tabs( $views ) 
	{

		$current_type = '';
		$active_class = ' nav-tab-active';

		if ( ! empty( $_REQUEST['jws_types'] ) ) {
			$current_type = $_REQUEST['jws_types'];
			$active_class = '';
		}

		$url_args = [
			'post_type' => 'jws_builder',
		];

		$baseurl = add_query_arg( $url_args, admin_url( 'edit.php' ) );

		$doc_types = smarthome_tb_types();
		?>

        <div id="smarthome-studio-wrapp"></div>
		<div id="smarthome-studio-theme-builder-tabs" class="nav-tab-wrapper">
			<a class="nav-tab<?php echo $active_class; ?>" href="<?php echo $baseurl; ?>">
				<?php echo  __( 'All', 'smarthome-studio' ); ?>
			</a>
			<?php
			foreach ( $doc_types as $type => $type_label ) :
				$active_class = '';

				if ( $current_type === $type ) {
					$active_class = ' nav-tab-active';
				}

				$type_url = add_query_arg( 'jws_types', $type, $baseurl );

				echo "<a class='nav-tab{$active_class}' href='{$type_url}'>{$type_label}</a>";
			endforeach;
			?>
		</div>
		<?php
		return $views;
	}

}
Smarthome_Studio_Post_Type::instance();