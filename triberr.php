<?php
/*
Plugin Name: Triberr
Plugin URI: http://triberr.com/subdomains/plugins/wordpress/
Description: Instantly send posts from your blog from Triberr.
Version: 1.0.0
Author: Triberr
Author URI: http://Triberr.com/
License: GPL2
*/
function triberr_grab_base_url() {
	$siteurl = get_option('siteurl');
	$baseurl = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__));
	echo $baseurl;
}


function triberr_admin_register_head() {
	$siteurl = get_option('siteurl');
    $styleurl = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/media/styles/manage.css';
    $jsurl = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/scripts/triberr_js.js';
    echo "<link rel='stylesheet' type='text/css' href='$styleurl' />\n";
    echo "<script type='text/javascript' src='$jsurl'></script>\n";
}

function triberr_menu() {
	add_options_page('Triberr Options', 'Triberr', 'manage_options', 'triberr-options', 'triberr_options_page');
}


add_action('admin_head', 'triberr_admin_register_head');

add_action('admin_menu', 'triberr_menu'); 


function triberr_options_page() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>
<div class="wrap">
<?php // echo triberr_connect('http://triberr.com/triber/subdomains/api/announce.php'); ?>
    <div class="top_nav">
        <div class="plugin_logo">
            <img src="http://triberr.com/assets/misc/logoLight.png" />
        </div>
	</div>
    
    <h2>Triberr Plugin Settings</h2>
    <div id="api" class="postbox" style="float:left;width:63%;">
    <h3 >API Blog Token</h3>
    	<div class="inside">
        <form method="post" action="options.php" class="form-table">
            <?php wp_nonce_field('update-options');
            $PINGKEY = get_option('triberr_apikey');
            ?>
            <ul>
            	<li>
                    Your API blog token connects this blog with your Triberr profile. <br />
                    You can find your token in your Triberr Account Settings on the Blog Settings page. <br /><br />
                    <small>Hint: Looks like a 35 character random string of numbers and letters</small> <br />
                    <input id="triberr_apikey" maxlength="35" size="35" name="triberr_apikey" value="<?php echo $PINGKEY; ?>" /> 
                    <small> <a href="#" onclick="toggle_visibility('api_help');">Help me find it</a></small></li>
            	</li>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="triberr_apikey" />
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                <br />
                <small>After saving, all future posts will automatically be sent to Triberr.</small>
            </ul>
        </form> 
       		 <div id="api_help" style="display: none;">
                 <hr />
            	<h4>Help Finding your API Blog Token</h4>
                If you are logged into Triberr.com <a href="http://triberr.com/settings/?pg=rss">Click Here</a> for your API token. <br />
                Your API Blog Token is a 35 character code acts as a unique identifier between Triberr and your blog. <br />
                <strong>Example:</strong> f31sP9di3z3jd93o93wM9dj390jfi2Lph39 <br /><br />
                
                Your API Blog Token can be found by logging into Triberr. Navigate to your "Global Account Settings" then your "Blog Settings". 
                Each blog will have a unique Blog API Token.			
                <h4>Video Guide</h4>
                <iframe width="420" height="315" src="http://www.youtube.com/embed/g3ORoLXjhnI" frameborder="0" allowfullscreen></iframe>
            </div>  
        </div>
	</div>
    
    <!--
    <div id="authorize" class="postbox" style="float:left;width:63%;">
    <h3 >Frictionless Guest Posting</h3>
    	<div class="inside">
        
        <iframe src="http://triberr.com/subdomains/plugins/fgp-authorize.php?key=<?php echo $PINGKEY; ?>&domain=<?PHP echo grab_base_url(); ?>" width="600" height="200"></iframe>
        
       
        </div>
	</div>
    -->
</div>



<?php
	//FORM CODE ENDS HERE
}



add_action('admin_footer', 'triberr_enable_remote'); 
add_action ('publish_post', 'triberr_submit_post');
add_action ('admin_footer', 'triberr_display_message');





function triberr_multiple($posts = NULL) {
	if($posts != NULL) {
		foreach($posts as $post) {
			$triberrURL = triberr_build_url($post);
			$triberrMSG = triberr_connect($triberrURL);
			echo "<p>".$triberrMSG."</p>";
		}
	} else {
		global $post;
		$thisCount = 1;
		$myposts = get_posts('numberposts=-1');
		foreach($myposts as $post) :
			setup_postdata($post);
			$triberrURL = triberr_build_url($post->ID);
			$triberrMSG = triberr_connect($triberrURL);
			echo "<p>".$triberrMSG."</p>";
		endforeach;
	}
}

function triberr_categories($PINGKEY = NULL) {
	$triberrCATS = triberr_connect('http://triberr.com/triber/subdomains/api/?key='.$PINGKEY.'&act=tribes');

	preg_match_all('#<id>(.*?)</id>#is', $triberrCATS, $PINFO[id], PREG_SET_ORDER);
	preg_match_all('#<name>(.*?)</name>#is', $triberrCATS, $PINFO[name], PREG_SET_ORDER);

	foreach($PINFO[id] as $key => $value) {
		if(get_option('triberr_category') === $PINFO[id][$key][1]) {
			echo '<option value="'.$PINFO[id][$key][1].'" selected=selected>'.$PINFO[name][$key][1].'</option>';
		} else {
			echo '<option value="'.$PINFO[id][$key][1].'">'.$PINFO[name][$key][1].'</option>';
		}
	}
}

//ACTUALLY PING THE PUBLISHED POST TO triberr
function triberr_build_url($post_ID) {
	$thisPost = get_post($post_ID, ARRAY_A);
	
	$fields = array(
		'act'=>urlencode("add"),
		'key'=>urlencode(get_option('triberr_apikey')),
		'url'=>urlencode(get_permalink( $post_ID )),
		'title'=>urlencode($thisPost['post_title']),
		'body'=>urlencode(apply_filters('the_content',$thisPost['post_content'])),  //apply_filters('the_content', $post->post_content)
		'status'=>urlencode($thisPost['post_status']),
		'guid'=>urlencode($thisPost['guid']),
		'post_date'=>urlencode($thisPost['post_date']),
		'post_id'=>urlencode($thisPost['ID']),
		'post_type'=>urlencode($thisPost['post_type']),
	);
	//echo "body: " . urlencode(apply_filters('the_content',$thisPost['post_content']));
	return $fields;
}

//SETTING UP THE OPTIONS PAGE
function triberr_connect($url, $fields) {
	//if (function_exists('curl_init')) {
		
		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string,'&');		
		
		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		
		//execute post
		$result = curl_exec($ch);
		
		//close connection
		curl_close($ch);
		//print $result;		 
		return $result;
//	} else {
	//	return file_get_contents($url);
	//}
}

function triberr_submit_post($post_ID) {
	$thisPost = get_post($post_ID, ARRAY_A);
			if(get_permalink( $post_ID ) != NULL) {
				$triberrFields = triberr_build_url($post_ID);
				$url = 'http://triberr.com/triber/subdomains/api/';
				$triberrMSG = triberr_connect($url, $triberrFields);
			}

	update_option('triberr_message', $triberrMSG);
}

function triberr_display_message() {
	if(get_option('triberr_message')) {
		echo '<div id="message" class="updated"><p>'.get_option('triberr_message').'</p></div>';
		update_option('triberr_message', '');
	}
}
function triberr_enable_remote (){
	$option_name = "enable_xmlrpc";
	$newvalue = "1";
	update_option($option_name, $newvalue);
}
?>
