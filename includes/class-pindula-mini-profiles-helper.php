<?php 

class Helper_Functions {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.52
	 */
	public function __construct() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/guzzlehttp/vendor/autoload.php';
	}
    
    public function quote_pwiki_fetch_first_paragraph( $page_titles ){

		$loginDetails = $this->sign_in_to_pindula_profiles_api();
		if( $loginDetails === false || 
			!isset($loginDetails[0]) || 
			!isset($loginDetails[1]) 
		){
			return false;
		}

		$client = $loginDetails[0];
		$cookieJar = $loginDetails[1];
		
		$multiple_title_extracts_url = 'https://contribute.pindula.co.zw/api.php'
			. '?action=query&format=json&formatversion=2&prop=extracts' 
			. '&redirects=1&exintro=&explaintext=&exlimit=3&titles='
			. $page_titles;

		$title_extracts = $this->quote_pwiki_poll_remote_api($client, $cookieJar, '', $multiple_title_extracts_url );
		$this->quote_pwiki_poll_remote_api( $client, $cookieJar, '', 'https://contribute.pindula.co.zw/api.php?action=logout&format=json' );
		return $title_extracts ;
    }
    
    public function update_snippets( $post_meta ){
    	try {
			$temp_meta = $post_meta;
			$page_titles = '';
			foreach( $temp_meta as $key => $profile ){
				if( $key !== 'last_updated' )  $page_titles .= $profile['title'] . '|';
			}

			$page_titles = preg_replace( ['/\|$/', '/\s/'], ["", "_"], $page_titles );
			$page_extracts = $this->quote_pwiki_fetch_first_paragraph( $page_titles );

			if( $page_extracts !== false ){
				$page_extracts = $page_extracts['query'];
				//use this to ensure correct extract for each title/page/article
				$extract_locations = array_column( $page_extracts['pages'], 'title' ); 

				foreach( $temp_meta as $key => $profile ){
					if( $key !== 'last_updated' ) {
						if( isset($page_extracts['redirects']) ){
							foreach( $page_extracts['redirects'] as $redirect){
								if( $redirect['from'] == $profile['title'] ){
									$profile['title'] = $redirect['to'];
								}
							}
						}
						$extract_key = array_search( $profile['title'], $extract_locations );
						if( $extract_key !== false ){						
							$post_meta[$key]['quote_pwiki_content'] = $page_extracts['pages'][$extract_key]['extract'];
						}
					}
				}

				$post_meta['last_updated'] = time();
			}else{
				return false;
			}
			return $post_meta;
		}catch (Exception $e) {
			return false;
		}
	}
	
	//functions for updating Pindula Profile/Page Titles every 12 hours
	public function p_mini_enable_frontend_ajax(){ 
		/*
		 * the data loaded by this request i.e Pindula Profile/Page Titles is only required when editing posts
		 * This data is therefore only required in admin side
		*/	
		if( is_user_logged_in() ){ ?>
			<script>
				jQuery(window).load(function(){
					jQuery.post(
						'<?php echo admin_url('admin-ajax.php');?>',
						{action:'p_mini_ajax_titles_refresh'}
					)
				});
			</script>
		<?php }
	}

	public function p_mini_ajax_titles_refresh(){
		$options = get_option( 'quote_pwiki' );
		$last_updated = $options['last_updated'];
		$current_time = time();
		$update_difference = $current_time - $last_updated;

		if( $update_difference > 43200 ){

			$p_mini__titles = $this->quote_pwiki_get_titles();
			if( $p_mini__titles !== false ){ 
				$options['quote_pwiki_titles'] = $p_mini__titles;   		
				$options['last_updated'] = time();
				update_option( 'quote_pwiki', $options );
			}
		}
		wp_die();
	}

	/*
	 * gets all the page titles from Pindula Profiles
	 * returns an array of all the page titles or false if any error occurs
	 * 
	 * @since    1.0.0
	*/
	public function quote_pwiki_get_titles(){
		
		//Ignore user aborts and allow the script to run for 2 minutes
        ignore_user_abort( true );
        set_time_limit( 120 ); 
        
        $loginDetails = $this->sign_in_to_pindula_profiles_api();

		if( $loginDetails === false || 
			!isset($loginDetails[0]) || 
			!isset($loginDetails[1]) 
		){		
			return false;
		}
        
        $client = $loginDetails[0];
        $cookieJar = $loginDetails[1];

		$profiles_batch = $this->quote_pwiki_poll_remote_api($client, $cookieJar, '');
		
		if( $profiles_batch === false ) return false;

		$all_profiles_titles = $profiles_batch['query']['allpages'];
		do{
			$continue = $profiles_batch['continue']['apcontinue'];
			$profiles_batch = $this->quote_pwiki_poll_remote_api($client, $cookieJar, $continue );
			if( $profiles_batch === false ) return false;
			
			$paged_titles = $profiles_batch['query']['allpages'];
			if( !is_array($paged_titles) || !is_array($all_profiles_titles)){
				return false;
			}
			$all_profiles_titles = array_merge($all_profiles_titles, $paged_titles);

		}while( isset( $profiles_batch['continue']['apcontinue'] ) );

		$this->quote_pwiki_poll_remote_api( $client, $cookieJar, '', 'https://contribute.pindula.co.zw/api.php?action=logout&format=json' );
		if( is_array($all_profiles_titles) ){
			return $all_profiles_titles;
		}else{
			return false;
		}
    }

	/*
     * @param $client:  current Guzzle client that has login session details
     * @param $cookieJar:  current session cookies that keep us logged in
     * @paged_titles: the API response which may contain errors, error info 
     * or the actual page titles we want
     * @param $continue: the offset to begin the next title list from
     * can be empty string for the initial list or MediaWiki URL encoded title  
     * @param $singleProfileUrl: a specific profile to retrive from the API. Used for single profiles
	 * @param $customUrl is a url to poll that works with get requests
	 * $customUrl should be provided if executing a simple GET request
	 * returns json result as array or false on http request failure
	 * 
	 * @since    2.52
	*/
    private function quote_pwiki_poll_remote_api($client, $cookieJar, $continue = '', $customUrl = NULL ){
		
		if( $customUrl === NULL){
			if( $continue !== ''){
				$apiUrl = 'https://contribute.pindula.co.zw/api.php?action=query&list=allpages&aplimit=500&format=json&apfrom=' . $continue;
			}else{
				$apiUrl = 'https://contribute.pindula.co.zw/api.php?action=query&list=allpages&aplimit=500&format=json';
			}
		}else{
			$apiUrl = $customUrl;
		}

        $response = $client->request('GET', $apiUrl, [
            'cookies' => $cookieJar
        ]);

        if( $response->getStatusCode() == 200 ){
			$decoded_json = json_decode( $response->getBody()->getContents(), true );
			if( $decoded_json == NULL || $decoded_json == FALSE ) return false;
			return $decoded_json;
        }
        return [];
	}

	/*
     * logs in to remote Pindula Contribute API
     * returns as elements in array the current session and cookies for this session
     * returns false if login failed
	 * 
	 * @since    2.52
    */ 
	private function sign_in_to_pindula_profiles_api(){
        
        //use SessionCookieJar to store the cookie data.
        //param 1: the session variable to store cookies to
        //$_SESSION['SESSION_STORAGE'] = .... store as json string in session ...
        //param 2: set to true to store the PHP SESSION COOKIE too. 
        $cookieJar = new GuzzleHttp\Cookie\SessionCookieJar('SESSION_STORAGE', true);

        $login = 'PindulaReader@PindulaMiniProfilesPlugin';
        $pass = 's67lr1n3deckvan7ikb0t7n80ssei304';

        $client = new \GuzzleHttp\Client();
        $res = $client->post(
            'https://contribute.pindula.co.zw/api.php',
            [
                'form_params' => [
                    'action' => 'query',
                    'meta' => 'tokens',
                    'type' => 'login',
                    'format'=> 'json'
                ],
                'cookies' => $cookieJar
            ]
        );

        $contents = $res->getBody()->getContents();
        $tokenContainer = json_decode( $contents );
        $token = $tokenContainer->query->tokens->logintoken;

        if( !empty($token) ){
            $res = $client->post(
                'https://contribute.pindula.co.zw/api.php',
                [
                    'form_params' => [
                        'action' => 'login',
                        'format'=> 'json',
                        'lgtoken' => $token,
                        'lgname' => $login,
                        'lgpassword' => $pass
                    ],
                    'cookies' => $cookieJar
                ]
            );
        }else{
            echo "<p>Failed to login to Pindula Profiles API.</p>";
            return false;
        }

        $logInResult = json_decode( $res->getBody()->getContents() );

        if( !empty($logInResult) ){
            if( $logInResult->login){
                if($logInResult->login->result == "Failed" ){
                    echo "<p>Failed to login to Pindula Profiles API: \n {$logInResult->login->reason}</p>";
                    return false;
                }else if($logInResult->login->result == "Success" ){
                    return [$client, $cookieJar];
                }
            }
        }else{
            echo "<p>Failed to login to Pindula Profiles API.</p>";
            return false;
        }
	}
}