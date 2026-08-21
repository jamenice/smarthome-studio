<?php
/**
 * Dynamic Tag: Image URL
 *
 * Exposes any WordPress attachment URL as a dynamic value for Elementor
 * background-image controls (and any other URL/image control).
 *
 * Usage: In Elementor editor, click the ⚡ icon on a background-image
 * control → choose "Image (Dynamic)" → pick or enter the source.
 */

namespace SmarthomeStudio\DynamicTags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;

if ( ! defined( 'ABSPATH' ) ) exit;

class Image_Dynamic_Tag extends Data_Tag {

    public function get_name(): string {
        return 'jws-image-dynamic-tag';
    }

    public function get_title(): string {
        return __( 'Image (Dynamic)', 'smarthome-studio' );
    }

    public function get_group(): string {
        return 'jws-dynamic';
    }

    public function get_categories(): array {
        return [
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
        ];
    }

    protected function register_controls(): void {
        $this->add_control(
            'source',
            [
                'label'   => __( 'Source', 'smarthome-studio' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'featured_image',
                'options' => [
                    'featured_image' => __( 'Featured Image', 'smarthome-studio' ),
                    'custom_field'   => __( 'Custom Field (ACF)', 'smarthome-studio' ),
                ],
            ]
        );

        $this->add_control(
            'meta_key',
            [
                'label'       => __( 'Meta Key', 'smarthome-studio' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => 'background_image',
                'condition'   => [ 'source' => 'custom_field' ],
            ]
        );

    }

    public function get_value( array $options = [] ): array {
        $source = $this->get_settings( 'source' );
        $id     = 0;
        $url    = '';

        if ( $source === 'featured_image' ) {
            $post_id = get_the_ID();
            $id      = get_post_thumbnail_id( $post_id );
            $url     = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';

        } elseif ( $source === 'custom_field' ) {
            $key = $this->get_settings( 'meta_key' );
            if ( $key ) {
                $val = get_post_meta( get_the_ID(), $key, true );
                // ACF returns attachment ID; plain meta may be a URL
                if ( is_numeric( $val ) ) {
                    $id  = (int) $val;
                    $url = wp_get_attachment_image_url( $id, 'full' );
                } else {
                    $url = (string) $val;
                }
            }

        }

        return [ 'id' => $id, 'url' => $url ];
    }
}
