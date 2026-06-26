<?php
/**
 * Studio Header Footer function
 *
 */

/**
 * Get content single builder.
 */
function jws_load_template_part() {
    $id = get_the_ID();
    $type = get_post_meta($id, 'jws_template_type', true); // 'true' to get single value
    $type = empty($type) ? 'tmp_header' : $type;

    switch ($type) {
        case 'tmp_header':
            $path = JWS_DIR_PATH . 'templates/content/header.php';
            break;
        case 'tmp_footer':
            $path = JWS_DIR_PATH . 'templates/content/footer.php';
            break;
        default:
            $path = JWS_DIR_PATH . 'templates/content/section.php';
    }

    load_template($path);
}

function smarthome_tb_types()
{ 
    $smarthome_tb_types = array(
        'tmp_header' => __('Header', 'smarthome-studio'),
        'tmp_footer' => __('Footer', 'smarthome-studio'),
        'tmp_single' => __('Single', 'smarthome-studio'),
        //'tmp_archive' => __('Archive', 'smarthome-studio'),
        'tmp_megamenu' => __('Mega Menu', 'smarthome-studio'),
        'tmp_custom_block' => __('Block', 'smarthome-studio'),
        'tmp_slider' => __('Slider', 'smarthome-studio'),
        'tmp_title_bar' => __('Title Bar', 'smarthome-studio'),
        //'tmp_pagetitle' => __('Page Title', 'smarthome-studio'),
    );

    return $smarthome_tb_types;
}

function smarthome_tb_get_template_type($post_id = '') {
    $post = get_post($post_id);
    $templates_types = smarthome_tb_types();
    if($post && get_post_type($post) === 'jws_builder') {
        $meta = get_post_meta( $post_id, 'jws_template_type', true );
        if( ! empty( $meta ) ) {
            return $meta;
        } else{
            return 'content';
        }
    }
    return false;
}


/**
 * Returns the appropriate file suffix based on script debugging settings.
 *
 * @return string The file suffix, '.min' if SCRIPT_DEBUG is false, empty otherwise.
 */
function jws_suffix() {
    return (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
}


/**
 * Fetches the header ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The header ID if set, false otherwise.
 */
function jws_get_header_id() {
    $header_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('tmp_header');
    return $header_id !== '' ? $header_id : false;
}

/**
 * Determines the activation status of the Header.
 *
 * @since  1.0.0
 * @return bool Returns true if the header is active, false if it is inactive.
 */
function jws_header_enabled() {
    return apply_filters('jws_header_enabled', jws_get_header_id() !== false);
}

/**
 * Returns the header template ID.
 *
 * @since  1.0.0
 * @return string|false The header template ID if set, false otherwise.
 */
function jws_header_template_id() {
    return apply_filters('jws_header_template_id', jws_get_header_id());
}

/**
 * Echoes the Header Template.
 *
 * @since  1.0.0
 */
function jws_get_header_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_header_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the header markup.
 *
 * @since  1.0.0
 */
function jws_render_header() {
    if (!jws_header_enabled()) {
        return;
    }
    ?>
    <header itemscope="itemscope" itemtype="http://schema.org/WPHeader">
        <?php jws_get_header_template(); ?> 
    </header>
    <?php
}

/**
 * Fetches the title bar ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The title_bar ID if set, false otherwise.
 */
function jws_get_title_bar_id() {
    $title_bar_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('tmp_title_bar');
    return $title_bar_id !== '' ? $title_bar_id : false;
}

/**
 * Determines the activation status of the title_bar.
 *
 * @since  1.0.0
 * @return bool Returns true if the title_bar is active, false if it is inactive.
 */
function jws_title_bar_enabled() {
    return apply_filters('jws_title_bar_enabled', jws_get_title_bar_id() !== false);
}

/**
 * Returns the title_bar template ID.
 *
 * @since  1.0.0
 * @return string|false The title_bar template ID if set, false otherwise.
 */
function jws_title_bar_template_id() {
    return apply_filters('jws_title_bar_template_id', jws_get_title_bar_id());
}


/**
 * Echoes the title_bar Template.
 *
 * @since  1.0.0
 */
function jws_get_title_bar_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_title_bar_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the header markup.
 *
 * @since  1.0.0
 */
function jws_render_title_bar() {
  if (!jws_title_bar_enabled()) {
        return;
    }
   
 jws_get_title_bar_template(); 
   
  
}

/**
 * Fetches the footer ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The footer ID if set, false otherwise.
 */
function jws_get_footer_id() {
    $footer_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('tmp_footer');
    return $footer_id !== '' ? $footer_id : false;
}

/**
 * Determines the activation status of the Footer.
 *
 * @since  1.0.0
 * @return bool Returns true if the footer is active, false if it is inactive.
 */
function jws_footer_enabled() {
    return apply_filters('jws_footer_enabled', jws_get_footer_id() !== false);
}

/**
 * Returns the footer template ID.
 *
 * @since  1.0.0
 * @return string|false The footer template ID if set, false otherwise.
 */
function jws_footer_template_id() {
    return apply_filters('jws_footer_template_id', jws_get_footer_id());
}

/**
 * Echoes the Footer Template.
 *
 * @since  1.0.0
 */
function jws_get_footer_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_footer_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the footer markup.
 *
 * @since  1.0.0
 */
function jws_render_footer() {
    if (!jws_footer_enabled()) {
        return;
    }
    ?>
    <footer itemscope="itemscope" itemtype="http://schema.org/WPFooter">
        <?php jws_get_footer_template(); ?>
    </footer>
    <?php
}


/**
 * Fetches the Before Header ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The before header ID if set, false otherwise.
 */
function jws_get_before_header_id() {
    $before_header_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('tmp_custom_block', 'before_header');
    return $before_header_id !== '' ? $before_header_id : false;
}

/**
 * Determines the activation status of the before_header.
 *
 * @since  1.0.0
 * @return bool Returns true if the before header is active, false if it is inactive.
 */
function jws_before_header_enabled() {
    return apply_filters('jws_before_header_enabled', jws_get_before_header_id() !== false);
}

/**
 * Returns the Before Header template ID.
 *
 * @since  1.0.0
 * @return string|false The before header template ID if set, false otherwise.
 */
function jws_before_header_template_id() {
    return apply_filters('jws_before_header_template_id', jws_get_before_header_id());
}

/**
 * Echoes the Before Header Template.
 *
 * @since  1.0.0
 */
function jws_get_before_header_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_before_header_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the Before Header markup.
 *
 * @since  1.0.0
 */
function jws_render_before_header() {
    if (!jws_before_header_enabled()) {
        return;
    }
    jws_get_before_header_template();
}

/**
 * Fetches the after header ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The after header ID if set, false otherwise.
 */
function jws_get_after_header_id() {
    $after_header_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('tmp_custom_block', 'after_header');
    return $after_header_id !== '' ? $after_header_id : false;
}

/**
 * Determines the activation status of the after_header.
 *
 * @since  1.0.0
 * @return bool Returns true if the after header is active, false if it is inactive.
 */
function jws_after_header_enabled() {
    return apply_filters('jws_after_header_enabled', jws_get_after_header_id() !== false);
}

/**
 * Returns the after header template ID.
 *
 * @since  1.0.0
 * @return string|false The after header template ID if set, false otherwise.
 */
function jws_after_header_template_id() {
    return apply_filters('jws_after_header_template_id', jws_get_after_header_id());
}

/**
 * Echoes the after header Template.
 *
 * @since  1.0.0
 */
function jws_get_after_header_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_after_header_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the after header markup.
 *
 * @since  1.0.0
 */
function jws_render_after_header() {
    if (!jws_after_header_enabled()) {
        return;
    }
    jws_get_after_header_template();
}

/**
 * Fetches the before footer ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The before footer ID if set, false otherwise.
 */
function jws_get_before_footer_id() {
    $before_footer_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('tmp_custom_block', 'before_footer');
    return $before_footer_id !== '' ? $before_footer_id : false;
}

/**
 * Determines the activation status of the before_footer.
 *
 * @since  1.0.0
 * @return bool Returns true if the before footer is active, false if it is inactive.
 */
function jws_before_footer_enabled() {
    return apply_filters('jws_before_footer_enabled', jws_get_before_footer_id() !== false);
}

/**
 * Returns the before footer template ID.
 *
 * @since  1.0.0
 * @return string|false The before footer template ID if set, false otherwise.
 */
function jws_before_footer_template_id() {
    return apply_filters('jws_before_footer_template_id', jws_get_before_footer_id());
}

/**
 * Echoes the before footer Template.
 *
 * @since  1.0.0
 */
function jws_get_before_footer_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_before_footer_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the before footer markup.
 *
 * @since  1.0.0
 */
function jws_render_before_footer() {
    if (!jws_before_footer_enabled()) {
        return;
    }
    jws_get_before_footer_template();
}

/**
 * Fetches the after footer ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The after footer ID if set, false otherwise.
 */
function jws_get_after_footer_id() {
    $after_footer_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('tmp_custom_block', 'after_footer');
    return $after_footer_id !== '' ? $after_footer_id : false;
}

/**
 * Determines the activation status of the after_footer.
 *
 * @since  1.0.0
 * @return bool Returns true if the after footer is active, false if it is inactive.
 */
function jws_after_footer_enabled() {
    return apply_filters('jws_after_footer_enabled', jws_get_after_footer_id() !== false);
}

/**
 * Returns the after footer template ID.
 *
 * @since  1.0.0
 * @return string|false The after footer template ID if set, false otherwise.
 */
function jws_after_footer_template_id() {
    return apply_filters('jws_after_footer_template_id', jws_get_after_footer_id());
}

/**
 * Echoes the after footer Template.
 *
 * @since  1.0.0
 */
function jws_get_after_footer_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_after_footer_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Renders the after footer markup.
 *
 * @since  1.0.0
 */
function jws_render_after_footer() {
    if (!jws_after_footer_enabled()) {
        return;
    }
    jws_get_after_footer_template();
}

/*-------------------------------------------------------------------------------------------------*/
/* Single Listing
/*-------------------------------------------------------------------------------------------------*/

/**
 * Determines the activation status of the Single Listing.
 *
 * @since  1.0.0
 * @return bool Returns true if the single listing is active, false if it is inactive.
 */
/*-------------------------------------------------------------------------------------------------*/
/* Single Room
/*-------------------------------------------------------------------------------------------------*/

/**
 * Determines the activation status of the Single Room.
 *
 * @since  1.0.0
 * @return bool Returns true if the single room is active, false if it is inactive.
 */
function jws_single_room_enabled() {
    return apply_filters('jws_single_room_enabled', jws_get_single_room_id() !== false);
}

/**
 * Returns the ID of the Single Room template.
 *
 * @since  1.0.0
 * @return int|false The ID or false if not set.
 */
function jws_get_single_room_id() {
    $single_room_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('single-room');
    return $single_room_id !== '' ? $single_room_id : false;
}

/**
 * Renders the Single Room template.
 *
 * @since  1.0.0
 */
function jws_render_single_room() {
    if (!jws_single_room_enabled()) {
        return;
    } ?>
        <?php jws_get_single_room_template(); ?>
    <?php
}

/**
 * Returns the template ID for the Single Room.
 *
 * @since  1.0.0
 * @return int The template ID.
 */
function jws_single_room_template_id() {
    return apply_filters('jws_single_room_template_id', jws_get_single_room_id());
}

/**
 * Outputs the Single Room Elementor template.
 *
 * @since  1.0.0
 */
function jws_get_single_room_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_single_room_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/*-------------------------------------------------------------------------------------------------*/
/* Single Agent
/*-------------------------------------------------------------------------------------------------*/

/**
 * Determines the activation status of the Single agent.
 *
 * @since  1.0.0
 * @return bool Returns true if the single agent is active, false if it is inactive.
 */
/*-------------------------------------------------------------------------------------------------*/
/* Single Agency
/*-------------------------------------------------------------------------------------------------*/

/**
 * Determines the activation status of the Single agency.
 *
 * @since  1.0.0
 * @return bool Returns true if the single agency is active, false if it is inactive.
 */
/*-------------------------------------------------------------------------------------------------*/
/* Single Post
/*-------------------------------------------------------------------------------------------------*/

/**
 * Determines the activation status of the Single post.
 *
 * @since  1.0.0
 * @return bool Returns true if the single post is active, false if it is inactive.
 */
function jws_single_post_enabled() {
    return apply_filters('jws_single_post_enabled', jws_get_single_post_id() !== false);
}

/**
 * Fetches the single post ID from the plugin settings.
 *
 * @since  1.0.0
 * @return string|false The single post ID if set, false otherwise.
 */
function jws_get_single_post_id() {
    $single_post_id = SmarthomeStudio\JWS_Render_Template::instance()->fetch_plugin_settings('single-post');
    return $single_post_id !== '' ? $single_post_id : false; 
}

/**
 * Renders the single post markup.
 *
 * @since  1.0.0
 */
function jws_render_single_post() {
    if (!jws_single_post_enabled()) {
        return;
    }?>
    <div class="htb-single-post-wrapper htb-single-post">
        <?php jws_get_single_post_template(); ?>
    </header>
    <?php
}

/**
 * Returns the single post template ID.
 *
 * @since  1.0.0
 * @return string|false The single post template ID if set, false otherwise.
 */
function jws_single_post_template_id() {
    return apply_filters('jws_single_post_template_id', jws_get_single_post_id());
}

/**
 * Echoes the single post Template.
 *
 * @since  1.0.0
 */
function jws_get_single_post_template() {
    echo SmarthomeStudio\JWS_Elementor::get_elementor_template(jws_single_post_template_id()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
