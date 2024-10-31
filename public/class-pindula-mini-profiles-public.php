<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.controvert.co/
 * @since      1.0.0
 *
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/public
 * @author     Controvert <support@controvert.co>
 */
class Pindula_Mini_Profiles_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.50
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.50
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.50
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pindula-mini-profiles-helper.php';


	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 * Styles not required in the current implementation
	 * Faster for load time ti use inline CSS
	 * JS is too small to require own file
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pindula_Mini_Profiles_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pindula_Mini_Profiles_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pindula-mini-profiles-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    2.50
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pindula_Mini_Profiles_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pindula_Mini_Profiles_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pindula-mini-profiles-public.js', array( 'jquery' ), $this->version, false );

	}

	public function pindula_mini_profiles_add_custom_rest_field() {
	    // schema for the pindula_mini_profiles field
	    $pindula_mini_profiles_schema = array(
	        'description'   => 'Pindula Mini Profiles snippets attached to the current post(maybe page?).',
	        'type'          => 'object',
	        'context'       =>   array( 'view' )
	    );
	     
	    // registering the pindula_mini_profiles field
	    register_rest_field(
	        'post',
	        'pindula_mini_profiles',
	        array(
	            'get_callback'      => array( $this, 'get_post_meta_for_rest'),
	            'update_callback'   => null,
	            'schema'            => $pindula_mini_profiles_schema
	        )
	    );
	}

	/* 
	* @param  Object    $object    The current post/page.
	* Use the $object to get post id then query post meta with it.
	*/
	public function get_post_meta_for_rest( $object, $field_name, $request ){

	 	$post_id = $object['id'];
	 	if( isset($post_id) && !is_wp_error($post_id) ){
			$post_meta = get_post_meta( $post_id, 'quote_pwiki_' . $post_id, true);
			if( empty($post_meta) ) return (new stdClass);
			return $this->output_mini_snippets( $post_meta, true );
		}
		return (new stdClass); 
	}

	public function should_display_pindula_mini_snippet($query){
		//don't run on archive pages that use the_excerpt because of unnecessary overhead
		if ( !( is_home() || is_archive() || is_search() ) ){ // && $query->is_main_query() ){
			add_filter('the_content', array($this, 'display_pindula_mini_snippet') );
        }
	}


	/* 
	* @param  string    $content    The current post content.
	* Pindula Mini Profiles are appended to the post content for display.
	*/
	public function display_pindula_mini_snippet($content){
		
		if( !(is_main_query()) || defined( 'REST_REQUEST' ) ) return $content; 

		$post_id = get_the_ID();
		$post_meta = get_post_meta( $post_id, 'quote_pwiki_' . $post_id, true);

		if( empty($post_meta) ) return $content;

		if( isset($post_meta['last_updated']) ){
			
			$current_time = time();
			$update_difference = $current_time - $post_meta['last_updated'];

			if( $update_difference > 43200 ){
				$hepler_functions = new Helper_Functions();
				$updated_post_meta = $hepler_functions->update_snippets( $post_meta );
			}

		}else{
			$hepler_functions = new Helper_Functions();
			$updated_post_meta = $hepler_functions->update_snippets( $post_meta );			
		}

		if( isset($updated_post_meta) && $updated_post_meta !== false ){	
			update_post_meta( $post_id, 'quote_pwiki_' . $post_id, $updated_post_meta );
			$post_meta = $updated_post_meta;
		}

		//remove unneeded property time of last update
		if( isset($post_meta['last_updated']) ){ unset( $post_meta['last_updated'] ); }
		if( count($post_meta) === 0 ){ return $content; }//for some reason God forbid the last_updated field is the only thing stored in meta

		//require_once( 'partials/pindula-mini-profiles-public-display.php' );
		$output_mini_snippets = $this->output_mini_snippets( $post_meta, false );
		return $content . $output_mini_snippets;
	}

	/* 
	* @param  array    $post_meta    The current Pindula Mini Profiles post meta.
	*/

	private function output_mini_snippets( $post_meta, $is_rest_response = false ){
	    $styles = '<style>.pindula-mini-snippet,.pindula-mini-snippet-single{padding:20px;border:2px solid #ddd}.pindula-mini-snippet{display:none}.active-snippet{display:block}.pindula-mini-title{display:inline-block;margin:0 5px -2px 0;padding:4px;text-align:center;background:#eee;border:2px solid transparent;max-width:32%}.active-title,.pindula-mini-title-single{border-right:2px solid #ddd;border-left:2px solid #ddd;border-top:2px solid #f60}.pindula-mini-title-single{width:max-content}.pindula-mini-title:hover{cursor:pointer}.active-title{color:#555;background:inherit;border-bottom:2px solid #fff}@media screen and (max-width:495px){.pindula-mini-titles{font-size:78%;display:flex;}}</style>';

	    if( $is_rest_response ){
	    	$post_meta['built_in_styles'] = $styles;
	    	$post_meta['js_controller'] = '<script>(function($){$(".pindula-mini-title").on("click",function(event){$(this).addClass("active-title").siblings().removeClass("active-title");$("#snippet-"+event.target.id).addClass("active-snippet").siblings().removeClass("active-snippet")})})(jQuery)</script>';
	    	unset( $post_meta['last_updated'] );
	    	return $post_meta;
	    }

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

	        usort($post_meta, array($this, 'sort_meta_by_position') );
	        
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

	private function sort_meta_by_position($a, $b) {
		if ( $a['position'] == $b['position']) return 0;
		return ( $a['position'] < $b['position'] ) ? -1 : 1;
	}


}
