<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.controvert.co/
 * @since      1.0.0
 *
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/admin
 * @author     Controvert <support@controvert.co>
 */
class Pindula_Mini_Profiles_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pindula-mini-profiles-helper.php';

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.50
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

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pindula-mini-profiles-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pindula-mini-profiles-admin.js', array( 'jquery' ), $this->version, false );
		//load data from database required by the JavaScript on the admin client side
		if( get_current_screen()->base == 'post' ){
			$data = $this->get_admin_required_params();
			wp_localize_script($this->plugin_name, 'mini_profiles_data', $data );
		}
	}

	//get the titles from url or database for Post editing page
	private function get_admin_required_params(){
		$profile_titles = $this->quote_pwiki_load_titles();
		$post_meta = get_post_meta( get_the_ID(), 'quote_pwiki_' . get_the_ID(), true);
		$post_titles['savedProfilesData'] = $post_meta; 

		//remove unneeded property time of last update
		if( isset($post_titles['savedProfilesData']['last_updated']) ){
			unset( $post_titles['savedProfilesData']['last_updated'] );
		}
		
		if( !empty($post_meta) ){
			foreach ($post_meta as $key => $value){
				if( $key !== 'last_updated' ) $post_titles['lowerCaseTitles'][] = strtolower($value['title']); 
			}
		}	
	
		return array( 
			"savedProfiles" => $post_titles,
			"profile_titles" => $profile_titles !== false ? $profile_titles : ''
		);
	}

	//get the titles from url or database for Post editing page
	private function quote_pwiki_load_titles(){

		$options = get_option( 'quote_pwiki' );	
		if( $options != '' && ( (time() - $options['last_updated']) <= 86400) ){
			$quote_pwiki_titles = $options['quote_pwiki_titles'];
			return $quote_pwiki_titles;
		}else{
			$helper_functions = new Helper_Functions();
			$qpwiki_titles = $helper_functions->quote_pwiki_get_titles();
			if( $qpwiki_titles !== false ){ 
				$options['quote_pwiki_titles'] = $quote_pwiki_titles = $qpwiki_titles;
				$options['last_updated'] = time();
				update_option( 'quote_pwiki', $options);
				return $quote_pwiki_titles;
			}else{
				return false;
			}
		}
	}


	public function pindula_mini_profiles_admin_menu(){
		add_options_page(
			'Pindula Mini Profiles',
			'Pindula Mini Profiles',
			'publish_posts',
			'pindula-mini-profiles',			
			array( $this, 'pindula_mini_profiles_options_page' )
		);
	}

	public function pindula_mini_profiles_options_page(){
		
		$options = get_option( 'quote_pwiki' );
		if( $options === false ) add_option( 'quote_pwiki', '', null, 'no' );
		if( !current_user_can( 'publish_posts') ) wp_die( 'You do not have sufficient permissions');
		
		if( isset($_POST['quote_pwiki_form_submitted']) ){

			$hidden_field = esc_html( $_POST['quote_pwiki_form_submitted'] );
			if( $hidden_field === 'U_P'){

				$helper_functions = new Helper_Functions();
				$all_articles = $helper_functions->quote_pwiki_get_titles();
				
				if( $all_articles !== false ){ 
					$options['quote_pwiki_titles'] = $all_articles;
					$options['custom_post_ids'] = $custom_post_ids;
					$options['last_updated'] = time();
					$update_result = update_option( 'quote_pwiki', $options );
					//$update_result = true;
				}else{
					$update_result = false;
				}
			}
		}
		require( 'partials/pindula-mini-profiles-admin-display.php');
	}

	public function pindula_mini_profiles_add_custom_box(){
		$screens = ['post', 'quote_pwiki'];
		foreach ($screens as $screen) {
			add_meta_box(
				'pindula-mini-profiles',// Unique ID
				'Pindula Mini Profiles',// Box title
				array( $this, 'quote_pwiki_custom_box_html' ),// Content callback, must be of type callable
				$screen,// Post type
				'side',
				'high'
			);
		}
	}
	
	public function quote_pwiki_custom_box_html(){

		$profile_titles = $this->quote_pwiki_load_titles();
		$profilesList = '<ul id="profilesList">';
		if( $profile_titles !== false ){
			foreach ($profile_titles as $key => $profile) {
				$profilesList .= '<li class=""><span class="searchItem" >' . $profile['title'] . '</span></li>';
			}
		}
		$profilesList .= '</ul>';

		echo '<div id="pmini-profiles" >' 
			. '<style>.shake{animation:shake .5s;animation-iteration-count:infinite}@keyframes shake{0%{transform:translate(1px,1px) rotate(0)}10%{transform:translate(-1px,-2px) rotate(-1deg)}20%{transform:translate(-3px,0) rotate(1deg)}30%{transform:translate(3px,2px) rotate(0)}40%{transform:translate(1px,-1px) rotate(1deg)}50%{transform:translate(-1px,2px) rotate(-1deg)}60%{transform:translate(-3px,1px) rotate(0)}70%{transform:translate(3px,1px) rotate(-1deg)}80%{transform:translate(-1px,-1px) rotate(1deg)}90%{transform:translate(1px,2px) rotate(0)}100%{transform:translate(1px,-2px) rotate(-1deg)}}.make_primary{float:right;background:none!important;color:#3366BB;border:none;padding:0!important;font: inherit;cursor: pointer;}.make_primary:hover{text-decoration: underline;cursor: pointer;}.pindula_editor_notice{background:aliceblue;box-shadow:0 4px 4px 0 rgba(0, 0, 0, 0.1), 0 4px 20px 0 rgba(0, 0, 0, 0);padding: 5px; font-weight:bold;}'
			. '#search-mini-profiles{background-image:url(https://www.w3schools.com/css/searchicon.png);background-position:10px 12px;background-repeat:no-repeat;width:100%;font-size:13px;padding:12px 20px 12px 40px;border:1px solid #ddd;margin-bottom:12px}#profilesList{list-style-type:none;padding:0;margin:0}.scrollableList{overflow:hidden;overflow-y:scroll;height:200px}#profilesList li span{border:1px solid #ddd;background-color:#f6f6f6;padding:4px;text-decoration:none;font-size:12px;color:#000;display:none;width:90%;margin:auto;cursor:pointer}#profilesList li span:hover:not(.header){background-color:#eee;cursor:pointer}</style>'
			. '<div class="pindula_editor_notice">You can search for a specific profile by typing keywords (from the profile title) below.</div>'
			. '<p class="search-mini-profiles"><input type="text" name="a" id="search-mini-profiles" placeholder="Search profiles directly"></p><div class="list">' . $profilesList . '</div>'
			. '</div>';

	}

	//These functions add post meta to the database
	public function pindula_mini_profiles_save_postmeta( $post_id ){

		if( !isset($_POST['pmini_profiles']) || count($_POST['pmini_profiles']) <= 0 ){
			delete_post_meta( $post_id, 'quote_pwiki_' . $post_id );
			return;
		}
		
		$post_meta = [];
		$quote_pwiki_page_title = $_POST['pmini_profiles'];
		
    	foreach ($quote_pwiki_page_title as $key => $profile_title) {
			
			if( preg_match('/\d$/', $profile_title, $output_position) ){
				$display_priority = $output_position[0];
			}else{
				$display_priority = "null";
			}
			
			if( preg_match('/(.*)_/', $profile_title, $output_title) ){
				$profile_title = $output_title[1];
			}else{	    		
				break; 
			}

			array_push( $post_meta, 
				array('position' => $display_priority,
					'title' => $profile_title,
					'quote_pwiki_content' => '',
				) 
			);
		}    	

		if( count($post_meta) > 0 ){

			$hepler_functions = new Helper_Functions();
			$post_meta = $hepler_functions->update_snippets( $post_meta );
			//insert/update post meta if any profile titles were loaded
			if( $post_meta !== false ){
				update_post_meta( $post_id, 'quote_pwiki_' . $post_id, $post_meta );
			}

		}
    
	}

}