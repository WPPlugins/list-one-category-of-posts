<?php
/*
Plugin Name: Category of Posts
Plugin URI: http://herselfswebtools.com/2007/10/here-is-a-wordpress-plugin-to-create-a-link-list-of-all-posts-in-one-category.html
Description: Displays a link list of posts in a specific category
Author: Linda MacPhee-Cobb
Version: 1.0
Author URI: http://herselfswebtools.com
*/


// Post a page for user to pick a category to be listed
add_option('category_number', '5');


function pc_add_option_pages() {
	if (function_exists('add_options_page')) {
		add_options_page('List Posts in a Category', 'Posts by category', 8, __FILE__, 'pc_options_page');
	}		
}


function pc_options_page() {
	if (isset($_POST['info_update'])) {

		?>
		<div id="message" class="updated fade"><p><strong><?php 

		$category_id = $POST['category_number'];
		update_option('category_number', (string) $_POST["category_number"]);
		echo "Configuration Updated!";

	    ?></strong></p></div><?php

	} ?>

	<div class=wrap>

	<h2>List list of posts for one category</h2>

	<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<input type="hidden" name="info_update" id="info_update" value="true" />

	<fieldset class="options"> 
	  <legend>Options</legend>
	  <table width="100%" border="0" cellspacing="0" cellpadding="6">

	  <tr valign="top"><td width="35%" align="right">
		  Category number
	  </td><td align="left">
		  <input name="category_number" type="text" size="50" value="<?php echo get_option('category_number') ?>"/>
	  </td></tr>
	  </table>
	</fieldset>

	<div class="submit">
		<input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />
	</div>
	</form>
	</div>
    <?php
}


function category_of_posts() {

	global $wpdb, $post;

	$category_id = get_option('category_number');
	$table_prefix = $wpdb->prefix;
	$the_output = NULL;


	/* fetch posts, categories from mysql and sort by category, then by post */
	$last_posts = (array)$wpdb->get_results("
	select ID, post_title, post_status, post_date, term_id, object_id, {$table_prefix}term_relationships.term_taxonomy_id, {$table_prefix}term_taxonomy.term_taxonomy_id  
	from {$table_prefix}posts, {$table_prefix}term_relationships, {$table_prefix}term_taxonomy 
	where ID = object_id 
	and {$table_prefix}term_relationships.term_taxonomy_id = {$table_prefix}term_taxonomy.term_taxonomy_id 
	and term_id = $category_id
	and taxonomy = 'category'
	and post_type != 'page'
	and post_status = 'publish' and post_date < NOW()
	order by post_title asc;	");

	if (empty($last_posts)) {
		return NULL;
	}


    /* this is what we print out on archives page */
	$the_output .= stripslashes($ddle_header); 


	/* print links and if category <a name header not printed print that first */
	$last_category = '';
	
	foreach ( $last_posts as $posts ){
		$title = $posts->post_title;
		$category = $posts->name;
		$post_number = $posts->ID;
		
		$the_output .= '<li><a href="' .get_permalink($post_number) . '">' . $title . '</a></li>';
	}
  
 
  
  return $the_output;

}


function pc_generate($content) {
	$content = str_replace("<!-- categoryofposts -->", category_of_posts(), $content);
	return $content;
}

add_filter('the_content', 'pc_generate');
add_action('admin_menu', 'pc_add_option_pages'); 



?>