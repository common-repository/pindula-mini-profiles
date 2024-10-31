<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://www.controvert.co/
 * @since      1.0.0
 *
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/public/partials
 */

function output_mini_snippets( $post_meta ){
    $styles = '<style>.pindula-mini-snippet,.pindula-mini-snippet-single{padding:20px;border:2px solid #ddd}.pindula-mini-snippet{display:none}.active-snippet{display:block}.pindula-mini-title{display:inline-block;margin:0 5px -2px 0;padding:4px;text-align:center;background:#eee;border:2px solid transparent;max-width:32%}.active-title,.pindula-mini-title-single{border-right:2px solid #ddd;border-left:2px solid #ddd;border-top:2px solid #f60}.pindula-mini-title-single{width:max-content}.pindula-mini-title:hover{cursor:pointer}.active-title{color:#555;background:inherit;border-bottom:2px solid #fff}@media screen and (max-width:400px){.pindula-mini-titles{font-size:78%}}</style>';

    if( count($post_meta) < 2 ){

        $quote_pwiki_snippet = '<div class="pindula-mini-profiles">'
        . $styles 
        . '<div class="pindula-mini-title-single" >' 
        . '<span class="single-title active-title-single">' . $post_meta[0]['title'] . '</span>'
        . '</div>' 
        . '<div class="pindula-mini-snippet-single" >'
        . '<section class="single-snippet active-snippet-single">'
        . '<p>' 
        . wp_trim_words( $post_meta[0]['quote_pwiki_content'] , 45, '...' )
        . ' Read More About '
        . '<a href="http://www.pindula.co.zw/' 
        . $post_meta[0]['title'] . '" target="_blank">'
        . $post_meta[0]['title'] . '</a>'
        . '</p>'
        . '</section>'
        . '</div>'
        . '</div>';

    }else{

        usort($post_meta, 'sort_by_position');
        
        $titles = '<div class="pindula-mini-titles" >';
        $snippets = '<div class="pindula-mini-snippets" >';

        for( $i = 0; $i < count( $post_meta ); $i++ ){
            //show the first profile by default
            $i == 0 ? $active_title = " active-title" : $active_title = "";
            $i == 0 ? $active_snippet = " active-snippet" : $active_snippet = "";
            $random_id_num = rand(0, 200);

            $titles .= '<span class="pindula-mini-title' . $active_title . '" id="' . $random_id_num . '">' 
            . $post_meta[$i]['title'] . '</span>';
            $snippets .= '<section class="pindula-mini-snippet' . $active_snippet . '" id="snippet-' . $random_id_num . '">'
            . '<p>' 
            . wp_trim_words( $post_meta[$i]['quote_pwiki_content'] , 45, '...' ) 
            . ' Read More About '
            . '<a href="http://www.pindula.co.zw/' 
            . $post_meta[$i]['title'] . '" target="_blank">'
            . $post_meta[$i]['title'] . '</a>'
            . '</p>'
            . '</section>';
        }

        $JavaScript_controller = '<script>(function($){$(".pindula-mini-title").on("click",function(event){$(this).addClass("active-title").siblings().removeClass("active-title");$("#snippet-"+event.target.id).addClass("active-snippet").siblings().removeClass("active-snippet")})})(jQuery)</script>';

        $quote_pwiki_snippet = '<div class="pindula-mini-profiles">'
            . $styles
            . $titles . '</div>' 
            . $snippets . '</div>'
            . $JavaScript_controller
            . '</div>';
    }
    return $quote_pwiki_snippet;
}

function sort_by_position($a, $b) {
    return $a['position'] - $b['position'];
}
