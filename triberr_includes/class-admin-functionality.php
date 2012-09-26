<?php
/**
 * Functions for the reblogging feature
 *
 * @package Triberr
 */
 
 // Create the function to output the contents of our Dashboard Widget
function triberr_dashboard_widget_function() {
	// Display whatever it is you want to show
	$PINGKEY = get_option('triberr_apikey');
	?>
    <iframe src="http://triberr.com/subdomains/plugins/tribal_stream/wp-dash-tribal-stream.php?key=<?php echo $PINGKEY; ?>&domain=<?PHP echo get_option('siteurl'); ?>" width="596" height="310"></iframe>
    <?PHP
} 

// Create the function use in the action hook
function triberr_add_dashboard_widgets() {
	
	$PINGKEY = get_option('triberr_apikey');
	if($PINGKEY != ""){
	wp_add_dashboard_widget('triberr_dashboard_widget', 'Tribal Stream from Triberr', 'triberr_dashboard_widget_function');
	
	// Globalize the metaboxes array, this holds all the widgets for wp-admin
	global $wp_meta_boxes;
	
	// Get the regular dashboard widgets array 
	// (which has our new widget already but at the end)

	$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
	
	// Backup and delete our new dashbaord widget from the end of the array

	$example_widget_backup = array('triberr_dashboard_widget' => $normal_dashboard['triberr_dashboard_widget']);
	unset($normal_dashboard['triberr_dashboard_widget']);

	// Merge the two arrays together so our widget is at the beginning
	$sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);

	// Save the sorted array back into the original metaboxes 
	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}
} 



function triberr_admin_setup_notices() {
	
    $PINGKEY = get_option('triberr_apikey');
	
	if($PINGKEY == ""){
		echo "<div class=\"error\"><p>Triberr plugin needs to be configured. Go to <a href=\"". $siteurl ."/wp-admin/options-general.php?page=triberr-options\">plugin settings</a>.</p></div>";	
	}
}

// Include stylesheets and javascripts
function triberr_admin_register_head() {
	$siteurl = get_option('siteurl');
    $styleurl = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/media/styles/manage.css';
    $jsurl = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/scripts/triberr_js.js';
    echo "<link rel='stylesheet' type='text/css' href='$styleurl' />\n";
    echo "<script type='text/javascript' src='$jsurl'></script>\n";
}




// show a message when a post is imported
function triberr_display_message() {
	if(get_option('triberr_message')) {
		echo '<div id="message" class="updated"><p>'.get_option('triberr_message').'</p></div>';
		update_option('triberr_message', '');
	}
}

// flips on xmlrpc so we can talk
function triberr_enable_remote (){
	$option_name = "enable_xmlrpc";
	$newvalue = "1";
	update_option($option_name, $newvalue);
}

// Displays the Triberr link in the sidebar
function triberr_menu() {
	add_options_page('Triberr Options', 'Triberr', 'manage_options', 'triberr-options', 'triberr_options_page');
	add_option( 'triberr_comments_width', '600', '', 'yes' );
	add_option( 'triberr_comments_bg_color', 'ffffff', '', 'yes' );
	add_option( 'triberr_comments_bg_color', 'ffffff', '', 'yes' );
	add_option( 'triberr_comments_status', 'on', '', 'yes' );
}




// Check if the user has permissions to edit the options page
function triberr_options_page() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
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
    
    <?php if($PINGKEY != ""){ ?>
    <div id="authorize" class="postbox" style="float:left;width:63%;">
    <h3 >ReBlog Settings</h3>
    	<div class="inside">
        
        <iframe src="http://triberr.com/subdomains/plugins/fgp-authorize.php?key=<?php echo $PINGKEY; ?>&domain=<?PHP echo get_option('siteurl'); ?>" width="600" height="160"></iframe>
        
       
        </div>
	</div>
    
    
    <div id="comment" class="postbox" style="float:left;width:63%;">
    <h3 >Triberr Comments</h3>
    	<div class="inside">
        
        <form method="post" action="options.php" class="form-table">
        <?php wp_nonce_field('update-options');
            $triberr_comments_bg_color = get_option('triberr_comments_bg_color');
            $triberr_comments_status = get_option('triberr_comments_status');
            $triberr_comments_width = get_option('triberr_comments_width');
			?>
        <ul>
            	<li>
                   Enable Triberr's commenting system?<br />
                   <small>Any comments from Triberr or any reblogged versions of this post will also be included</small> <br />
                   <select name="triberr_comments_status" id="triberr_comments_status">
                   	<option value="on" <?php if($triberr_comments_status == "on"){ echo "selected=\"selected\""; } ?>>On</option>
                    <option value="off" <?php if($triberr_comments_status == "off"){ echo "selected=\"selected\""; } ?>>Off</option>
                   </select>
                 </li>  
                   <br />
                <li>
                   Look and feel<br />
                   <small>This will control the look of the comments on your site</small> <br />
                    Background color: #<input name="triberr_comments_bg_color" id="triberr_comments_bg_color" type="text" value="<?php echo $triberr_comments_bg_color; ?>" style="width: 70px;"/><br />
                    Width: <input name="triberr_comments_width" id="triberr_comments_width" type="text" value="<?php echo $triberr_comments_width; ?>" style="width: 50px;"/>px<br />
                   
                 </li>     
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="page_options" value="triberr_comments_bg_color,triberr_comments_width,triberr_comments_status" />
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
              
            </ul>
        </form> 
       
        </div>
	</div>
   
   <?PHP } ?> 
    
    
    
</div>



<?php
	//FORM CODE ENDS HERE
}