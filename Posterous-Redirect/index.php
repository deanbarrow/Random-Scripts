<?php 
/*
 * Code by Dean Barrow (http://deanbarrow.co.uk)
 * 5th July 2011
 * 
 * Quick and dirty script to retain your Wordpress links when transferring your site to Posterous.
 * Links are 301 (permanently) redirected to your new posterous site.
 * Will only follow posts, categories and tags. I have not implemented pages as you can easily
 * create these in Posterous.
 * I ignored dates as no one links/clicks these anyway..
 * 
 * You cannot use the same URL on both old and new sites. For example:
 * if your old site is 'http://www.domain.com' you must drop the www
 * for your new site: 'http://domain.com' and vica versa.
 * Run this script from the location of your old site (http://www.domain.com),
 * and point your new domain (http://domain.com) to Posterous.
 * 
 * Leave this script to run for a few weeks/months until search engines have reindexed
 * all your links. Run permanently if you have a lot of backlinks.
 * Hopefully Posterous will release an update to replace this script in the future.
 * 
 * 
 * Usage:	Export your site from Wordpress (Tools > Export) and rename it to export.xml
 * 			Put export.xml in the same directory as this file.
 * 			Important: Make sure your old domain points to this folder!
 * 			Set $new_url to your new domain or posterous account.
 */
$new_url = "http://domain.com"; // or http://username.posterous.com

/* DO NOT CHANGE ANYTHING BELOW THIS LINE */

if(!file_exists('export.xml')) die('Error: export.xml missing!');

// Get requested URL
$request = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
// If no trailing '/' then add one - saves messing around later
if (substr($request, -1) != '/') $request .= '/';

// Get xml and loop through it
$xml = simplexml_load_file('export.xml');
$old_url = $xml->channel->link; 
foreach ($xml->channel->item as $item) { 
	$link = $item->link;

	// If requested page matches a previous post..
	if ($request == $link){

		// Slug is made up from post title, max 45 chars
		// I admit this next line is dirty, I had a few bits to remove: ' - ', '(', ')', '.' I could of put this in an array but whatever
		$slug = substr(strtolower(str_replace("--", "-", str_replace("--", "-", str_replace(" ", "-", str_replace(".", "", str_replace("(", "", str_replace(")", "", $item->title))))))),0, 45);
		
	/* I removed this as I originally thought the slug would be the wordpress URL,
	 * I left it in incase Posterous ever defaulted to the slug instead of title.
	 */
		// Remove date info from link to leave just the slug/post name
		//$slug = explode("/",$_SERVER["REQUEST_URI"]);
		//$slug = $slug[sizeof($slug)-2];
		
		// Redirect to http://domain.com/slug
		redirect("$new_url/$slug");
	}
	
	// Match tags/categories
	$temp = $item->category;
	for ($i=0;$i<sizeof($temp);$i++){
		
		$first = true;	
		foreach ($temp[$i]->attributes() as $a => $b) {
			
			// first pass gives category/post_tag tag, second pass gives contents
			if ($first){
				if ($b == "category") $next = "category";
				if ($b == "post_tag") $next = "post_tag";
				$first = false;
			}else{
				// attempt to match category
				if ($next == "category"){
					if ($request == "$old_url/category/$b/") redirect("$new_url/tag/$b/");
				}
				
				// attempt to match tag
				if ($next == "post_tag"){
					if ($request == "$old_url/tag/$b/") redirect("$new_url/tag/$b/");
				}
			}
			
		}
	}
} 

// If script has run through all posts/cats/tags without matching, just 301 to homepage
redirect($new_url);

function redirect($url){
	header("HTTP/1.1 301 Moved Permanently");
	header ("Location: $url");
	exit();	
}
?>
