<?php

namespace ILI\FAUTemplates;

defined('ABSPATH') || exit;

class Shortcodes {
    
    function __construct() {
        add_shortcode('ilifautpl_topic_boxes', array($this, 'ilifautpl_shortcode_topic_boxes'));
        add_shortcode('themenboxen', array($this, 'ilifautpl_shortcode_topic_boxes'));
    }

    function ilifautpl_shortcode_topic_boxes( $atts ) {
        extract( shortcode_atts( array (
            'ids' => '',
        ), $atts ) );

        if( ! $ids )
            return '';
        
        $ids = str_replace(' ', '', $ids);
        $ids = explode(',', $ids);

        foreach( $ids as $key => $id ) {
            $ids[$key] = (int)$id;
        }

        $args = array(
            'post_type' => 'ilifautpl_topic_box',
            'include' => $ids,
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'post__in',
        );

        $topic_boxes = get_posts( $args );

        if( empty( $topic_boxes ) )
            return;

        $topic_box_excerpt_length = 150;

        $html = '<div class="ilifautpl-topic-boxes">';
            foreach( $topic_boxes as $box ) {
                $target_id = get_post_meta( $box->ID, '_ilifautpl_topic_box_target_id', true );

                if( ! $target_id )
                    continue;
                    
                $topic_box_url = esc_url( get_permalink( $target_id ) );
                $topic_box_excerpt = preg_replace('/\s+?(\S+)?$/', '', substr($box->post_content, 0, $topic_box_excerpt_length)) . '&hellip;';
                
                $html .= '<div class="ilifautpl-topic-box">';
                    $html .= '<div aria-hidden="true" role="presentation" tabindex="-1" class="passpartout" itemprop="image" itemscope="" itemtype="https://schema.org/ImageObject">';
                        $html .= '<meta itemprop="url" content="' . get_the_post_thumbnail_url( $box->ID ) . '">';
                        $html .= '<a href="' . $topic_box_url . '">';
                            $html .= get_the_post_thumbnail(
                                $box->ID,
                                'ilifautpl-topic-box',
                                array(
                                    'class' => 'ilifautpl-topic-box-image',
                                    'itemprop' => 'thumbnailUrl',
                                )
                            );
                        $html .= '</a>';
                    $html .= '</div>';
                    $html .= '<h3 itemprop="title"><a href="' . $topic_box_url . '">' . $box->post_title . '</a></h3>';
                    $html .= '<p itemprop="description">' . $topic_box_excerpt . ' <a aria-hidden="true" tabindex="-1" href="' . $topic_box_url . '">' . __('Weiterlesen', 'ilifautpl') . '</a><span class="screen-reader-text">' . __('Weiterlesen', 'ilifautpl') . '</span></p>';
                $html .= '</div>';
            }
        $html .= '</div>';

        return $html;
    }
}