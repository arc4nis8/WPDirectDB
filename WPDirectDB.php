<?php
////////////////////
// WPDirectDB.php //
////////////////////

class WPDirectDB
{

private $connection = null;
private $wp_database_prefix = false;

// Constructor
public function __construct($db_host, $db_user, $db_pass, $db_charset, $wp_tables_prefix, $error_file, $error_line, $set_database=false, $db_name="")
{
	// The prefix for the wordpress tables
	$this->wp_tables_prefix = $wp_tables_prefix;

	// Open the mysql connection
	if ($set_database == true){
		$this->connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name)
		or error(mysqli_connect_error(), $error_file, $error_line);
	}else{
		$this->connection = mysqli_connect($db_host, $db_user, $db_pass)
		or error(mysqli_connect_error(), $error_file, $error_line);
	}
	mysqli_set_charset($this->connection, $db_charset);
}

// Destructor
public function __destruct()
{
	// Close the mysql connections
	mysqli_close($this->connection);
}

/*** Public ***/

// Get all posts in a category using the id of the category (term_id)
public function get_posts_bycategory($term_id_or_title, $error_file, $error_line)
{
	$posts_final_arr = array();

	// If the input is a title, find the term_id
	if (is_string($term_id_or_title)){
		$term_id = $this->get_term_id($term_id_or_title, $error_file, $error_line);
	}else{
		$term_id = $term_id_or_title;
	}
	if ($term_id === false){
		trigger_error("WBDirectDB: Cannot get term_id from the title of the category.", E_USER_NOTICE);
		return false; // Error
	}

	// Get the term_taxonomy_id from the term_id
	$term_taxonomy_id_result= $this->select("SELECT term_taxonomy_id FROM ".$this->wp_tables_prefix."term_taxonomy WHERE term_id=?",
	"i", array(&$term_id), $error_file, $error_line);
	if ($term_taxonomy_id_arr = mysqli_fetch_array($term_taxonomy_id_result)){
		$term_taxonomy_id = $term_taxonomy_id_arr["term_taxonomy_id"];
	}else{
		trigger_error("WBDirectDB: Cannot get term_taxonomy_id from the term_id.", E_USER_NOTICE);
		return false; // Error
	}

	// Get all the posts id with the selected category id
	$posts_id_result = $this->select("SELECT object_id FROM ".$this->wp_tables_prefix."term_relationships WHERE term_taxonomy_id=?",
	"i", array(&$term_taxonomy_id), $error_file, $error_line);

	// Put all the posts id in a numeric array
	$posts_id_arr = $this->fetch_all($posts_id_result);

	// If there are no post ids
	if (empty($posts_id_arr)) {
		trigger_error("WBDirectDB: Did not find any post id.", E_USER_NOTICE);
		return false; // Error
	}

	// Loop through all the posts
	foreach ($posts_id_arr as $post_id){

		// Get the post with the selected id
		$id = $post_id["object_id"];
		$posts_result = $this->select("SELECT * FROM ".$this->wp_tables_prefix."posts WHERE id=?",
		"i", array(&$id), $error_file, $error_line);

		// Fetch one post
		if ($posts_one = mysqli_fetch_array($posts_result, MYSQLI_ASSOC)){

			// If the post is not publishd (a draft, or in trash...) dont add it
			if ($posts_one["post_status"] != "publish") continue;

			// Get the postmeta fields
			$postmeta_result = $this->select("SELECT * FROM ".$this->wp_tables_prefix."postmeta WHERE post_id=?",
			"i", array(&$id), $error_file, $error_line);
			$postmeta_arr = $this->fetch_all($postmeta_result);

			// Loop through all the post meta fields and add them to the main post
			foreach ($postmeta_arr as $postmeta_one){

				// If its a normal meta field
				$meta_key = $postmeta_one["meta_key"];
				if ($meta_key[0] !== "_"){
					$posts_one[$meta_key] = $postmeta_one["meta_value"];
				}

				// If its an attachment, go and find the file
				if (strpos($meta_key, "_attachment_") !== false){
					$postmeta_id = $postmeta_one["meta_value"];
					$attached_file = "_wp_attached_file";
					$postmeta_result = $this->select("SELECT * FROM ".$this->wp_tables_prefix."postmeta WHERE post_id=? AND meta_key=?",
					"ss", array(&$postmeta_id, &$attached_file), $error_file, $error_line);
					if ($postmeta_fetch = mysqli_fetch_array($postmeta_result, MYSQLI_ASSOC)){
						$posts_one[$meta_key] = $postmeta_fetch["meta_value"]; // The path to the file
					}
				}

			}

			// Push the post to the final array
			array_push($posts_final_arr, $posts_one);

		}

	}

	// If there are no posts
	if (empty($posts_final_arr)) {
		trigger_error("WBDirectDB: Did not find any posts.", E_USER_NOTICE);
		return false; // Error
	}

	return $posts_final_arr;
}

// Generic MySQL select with prepared statements
public function select($query, $typestructure, $paramref, $error_file, $error_line)
{
	$sql_stmt = mysqli_prepare($this->connection, $query)or $this->error(mysqli_error($this->connection), $error_file, $error_line);
	call_user_func_array("mysqli_stmt_bind_param",array_merge(array($sql_stmt, $typestructure), $paramref)); 
	mysqli_stmt_execute($sql_stmt)or $this->error(mysqli_error($this->connection), $error_file, $error_line);
	$result = mysqli_stmt_get_result($sql_stmt);
	mysqli_stmt_close($sql_stmt)or $this->error(mysqli_error($this->connection), $error_file, $error_line);
	return $result;
}

// Fetch all the results to a numeric array
public function fetch_all($result)
{
	$return_arr = array();
	while ($row = mysqli_fetch_array($result)){
		array_push($return_arr, $row);
	}
	return $return_arr;
}

// Sort the returned post array by the date - post_modified (default) or post_date
public function sort_by_date($posts_arr, $order=SORT_ASC ,$sort_by="post_modified")
{
	foreach ($posts_arr as $key=>$part) {
		$sort[$key] = strtotime($part[$sort_by]);
	}
	array_multisort($sort, $order, $posts_arr);
	return $posts_arr;
}

/*** Private ***/

// Get the term_id from the title of the category
private function get_term_id($category_title, $error_file, $error_line)
{
	// Get the term_id
	$term_id_result= $this->select("SELECT term_id FROM ".$this->wp_tables_prefix."terms WHERE name=?",
	"s", array(&$category_title), $error_file, $error_line);

	// Fetch and return the value
	if ($term_id_arr = mysqli_fetch_array($term_id_result)){
		return $term_id_arr["term_id"]; 
	}else{
		return false; // Error, category not found
	}
}

// Trigger MySQL error
private function error($logtext, $error_file, $error_line)
{
    mysqli_rollback($this->connection);
	trigger_error($logtext." in file ".$error_file." on line ".$error_line, E_USER_ERROR);
}

}
?>