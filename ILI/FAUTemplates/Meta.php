<?php

namespace ILI\FAUTemplates;

defined('ABSPATH') || exit;

class Meta {
    
    protected $templates;
    
    public function __construct() {
        add_action( 'add_meta_boxes', array($this, 'ilifautpl_add_meta_boxes') );
        add_action( 'save_post', array($this, 'ilifautpl_save_meta_boxes') );
        add_action( 'wp_ajax_ilifautpl_get_slide_image', array($this, 'wp_ajax_ilifautpl_get_slide_image') );
    }
    
    public function ilifautpl_add_meta_boxes() {
        global $post;
        
        $screens = get_post_types();
        
        foreach ( $screens as $screen ) {
            $template = get_post_meta( $post->ID, '_wp_page_template', true );
            
            // Frontpage slides
            if( 'templates/template-frontpage.php' === $template ) {
                
                wp_enqueue_media();
                
                add_meta_box(
                    'ilifautpl-slides',
                    esc_html__( 'Slider (ILI FAU Templates)', 'mnmlwp' ),
                    array($this, 'frontpage_slides_callback'),
                    $screen
                );
            }
        }
    }
    
    public function frontpage_slides_callback() {
        wp_nonce_field( 'ilifautpl_meta_boxes_nonce', 'ilifautpl_meta_boxes_nonce' );

        $slides = get_post_meta( get_the_ID(), '_ilifautpl_slides', true );
        
        if( empty( $slides ) ) {
            $slides = array();
            $slides[0] = '';
        }

        foreach( $slides as $key => $slide ) {
            $id = (int)$key + 1;
            $url = isset( $slide['url'] ) ? $slide['url'] : '';
            $link = isset( $slide['link'] ) ? $slide['link'] : '';
            $headline = isset( $slide['headline'] ) ? $slide['headline'] : '';
            $subtitle = isset( $slide['subtitle'] ) ? $slide['subtitle'] : '';
            
            echo '<div class="ilifautpl-input-slide-wrapper" id="ilifautpl-input-slide-wrapper-' . $id . '" data-id="' . $id . '">';
            echo '<label class="ilifautpl-label" for="ilifautpl-frontpage-slides">Slide ' . $id . '</label>';
            echo '<input class="ilifautpl-input ilifautpl-input-slide" type="text" id="ilifautpl-input-slide-urls" name="ilifautpl-input-slide-urls[]" value="' . $url . '" placeholder="URL&hellip;">';
            echo '<div class="ilifautpl-input-slide-url-buttons"><a class="button ilifautpl-input-slide-media">' . __('Media', 'ili-fau-templates') . '</a><a class="button ilifautpl-remove-slide">' . __('Löschen', 'ilifautpl') . '</a></div>';
            echo '<input class="ilifautpl-input ilifautpl-input-slide-link" type="text" id="ilifautpl-input-slide-links" name="ilifautpl-input-slide-links[]" value="' . $link . '" placeholder="Link&hellip;">';
            echo '<input class="ilifautpl-input ilifautpl-input-slide-headline" type="text" id="ilifautpl-input-slide-headlines" name="ilifautpl-input-slide-headlines[]" value="' . $headline . '" placeholder="Überschrift&hellip;">';
            echo '<textarea class="ilifautpl-input ilifautpl-input-slide-subtitle[]" id="ilifautpl-input-slide-subtitles" name="ilifautpl-input-slide-subtitles[]" placeholder="Schlagzeile&hellip;">' . $subtitle . '</textarea>';
            echo '</div>';
        }

        echo '<a class="button ilifautpl-add-slide">' . __('Slide hinzufügen', 'ili-fau-templates') . '</a>';
        echo '<br><br><input type="submit" name="submit" id="submit" class="button button-primary button-ilifautpl-save" value="Änderungen speichern">';
    }
    
    // Refresh slide preview image
    function ilifautpl_get_slide_image() {
        if( isset( $_GET['id'] ) ) {
            $image = wp_get_attachment_image( filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT ), 'medium', false, array( 'id' => 'ilifautpl-slide-preview' ) );
            
            $data = array(
                'image'    => $image,
            );
            
            wp_send_json_success( $data );
        } else {
            wp_send_json_error();
        }
    }
    
    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id
     */
    function ilifautpl_save_meta_boxes( $post_id )
    {
        if ( ! isset( $_POST['ilifautpl_meta_boxes_nonce'] ) )
            return;

        if ( ! wp_verify_nonce( $_POST['ilifautpl_meta_boxes_nonce'], 'ilifautpl_meta_boxes_nonce' ) )
            return;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }
        
        // Sanitize user input.
        $urls = $_POST['ilifautpl-input-slide-urls'];
        $links = $_POST['ilifautpl-input-slide-links'];
        $headlines = $_POST['ilifautpl-input-slide-headlines'];
        $subtitles = $_POST['ilifautpl-input-slide-subtitles'];
        
        $slides = array();
        
        foreach( $urls as $key => $url ) {
            array_push( $slides, array(
                'url' => filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '',
                'link' => isset( $links[$key] ) && filter_var( $links[$key], FILTER_VALIDATE_URL ) ? $links[$key] : '',
                'headline' => isset( $headlines[$key] ) ? sanitize_text_field( $headlines[$key] ) : '',
                'subtitle' => isset( $subtitles[$key] ) ? sanitize_text_field( $subtitles[$key] ) : '',
            ) );
        }

        // Save
        update_post_meta( $post_id, '_ilifautpl_slides', $slides );
    }
}
