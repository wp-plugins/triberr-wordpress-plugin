<?php
function triberr_comment_template( $comment_template ) {
	global $post;
	//$triberrPostType = $post->post_type;
	
	if($post->post_type == 'post'){
	include  'triberr-comments.php';
	}
}	
?>