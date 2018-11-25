# WPDirectDB
PHP Class wrapper that helps with reading the **Wordpress** database.

&nbsp;&nbsp;

---

&nbsp;&nbsp;

## 1.0 Why use WPDirectDB
**Wordpress** is the most popular CMS currently, however it's also a bloated solution. There is one thing where **Wordpress** shines, and that is it's user friendly admin interface. For managing posts and media files **Wordpress** is a robust and easy to use solution. **WPDirectDB** is meant to help with building a **decoupled Wordpress solution**, leaving the flexibility to build a custom frontend. When you need to connect to the **Wordpress** database including the whole **Wordpress** backend code is a big performance hit. **WPDirectDB** is an easy solution to get posts directly from the database, without the need to include **Wordpress** in you frontend code.

&nbsp;&nbsp;

---

&nbsp;&nbsp;

## 2.0 Installation
Download the **WPDirectDB.php** file and copy it to your website root directoy or a subdirectory.

&nbsp;&nbsp;

---

&nbsp;&nbsp;

## 3.0 Include
Include the **WPDirectDB.php** file in your project:
```php
include "/path/to/WPDirectDB.php";
```

&nbsp;&nbsp;

---

&nbsp;&nbsp;

## 4.0 Example
```php
// Include the WPDirectDB class
include "/path/to/WPDirectDB.php";

// Create an instance of the WPDirectDB class
$m_WPDirectDB = new WPDirectDB("database_host", "database_user", "database_user_password",
"utf8mb4", "wp_", __FILE__, __LINE__, true, "database_name");

// Get all the posts with the news category tag in an array
$posts_news = $m_WPDirectDB->get_posts_bycategory("news", __FILE__, __LINE__);

// Print all the titles of the news posts
foreach ($posts_news as $news){
  echo "<span>".$news["post_title"]."</span><br/>"
}
```

&nbsp;&nbsp;

---

&nbsp;&nbsp;

## 5.0 Reference

### 5.1 The constructor
```php
public function __construct($db_host, $db_user, $db_pass, $db_charset, $wp_tables_prefix,
$error_file, $error_line, $set_database=false, $db_name="")
```

&nbsp;&nbsp;

**$db_host** *string*

The host of the database, ex. *localhost*.

&nbsp;&nbsp;

**$db_user** *string*

The name of the user that connects to the database.

&nbsp;&nbsp;

**$db_pass** *string*

Password of the user that connects to the database.

&nbsp;&nbsp;

**$db_charset** *string*

Character set of the database, ex. *utf8mb4*.

&nbsp;&nbsp;

**$wp_tables_prefix** *string*

The prefix of the wordpress tables, the standard prefix that Wordpress suggest is *wp_*.

&nbsp;&nbsp;

**$error_file** *string*

Use `__FILE__` in order to get the name of the file if an error occurs.

&nbsp;&nbsp;

**$error_line** *string*

Use `__LINE__` in order to get the line number of the file if an error occurs.

&nbsp;&nbsp;

**$wp_tables_prefix** *string*

The prefix of the wordpress tables, the standard prefix that Wordpress suggest is *wp_*.

&nbsp;&nbsp;

**$set_database** *boolean*

Optional, default is `false`. Set it to `true` if you want to specify a database to use.

&nbsp;&nbsp;

**$db_name** *string*

Optional, default is a blank string. If `$set_database=true` then specify the name of the database.

&nbsp;&nbsp;

---

&nbsp;&nbsp;

### 5.2 Get posts
```php
public function get_posts_bycategory($term_id_or_title, $error_file, $error_line)
```

&nbsp;&nbsp;

**Return**

The function returns an *array* of posts or `false` if an error occured.

&nbsp;&nbsp;

**$term_id_or_title** *string* or *integer*

The name of the category of the posts to get. Also you can specify the ID number of the category. Using the ID instead of the category name is faster, since there is one less MySQL call to make.

&nbsp;&nbsp;

**$error_file** *string*

Use `__FILE__` in order to get the name of the file if an error occurs.

&nbsp;&nbsp;

**$error_line** *string*

Use `__LINE__` in order to get the line number of the file if an error occurs.

&nbsp;&nbsp;

---

&nbsp;&nbsp;

### 5.3 Select
```php
public function select($query, $typestructure, $paramref, $error_file, $error_line)
```

&nbsp;&nbsp;

**Return**

The function returns a list of MySQL results or `false` if an error occured.

&nbsp;&nbsp;

**$query** *string*

The MySQL query.

&nbsp;&nbsp;

**$typestructure** *string*

String of the data types (string or integer), ex. string-integer-integer-string `"siis"`.

&nbsp;&nbsp;

**$paramref** *array*

An array of pointers to the variables that are used in the select statement.

&nbsp;&nbsp;

**$error_file** *string*

Use `__FILE__` in order to get the name of the file if an error occurs.

&nbsp;&nbsp;

**$error_line** *string*

Use `__LINE__` in order to get the line number of the file if an error occurs.

&nbsp;&nbsp;

---

&nbsp;&nbsp;

### 5.4 Fetch all results
```php
public function fetch_all($result)
```

&nbsp;&nbsp;

**Return**

The function returns an *array* of results or `false` if an error occured.

&nbsp;&nbsp;;

**$result** *string*

The result of a MySQL call.

&nbsp;&nbsp;

---

&nbsp;&nbsp;

### 5.5 Sort by date
```php
public function sort_by_date($posts_arr, $order=SORT_ASC ,$sort_by="post_modified")
```

&nbsp;&nbsp;

**Return**

The function returns an *array* of posts sorted by the date or `false` if an error occured.

&nbsp;&nbsp;

**$posts_arr** *array*

An array of all the posts to sort.

&nbsp;&nbsp;

**$order** *integer*

Optional, default is `SORT_ASC`. Sort ascending or descending. `SORT_ASC` or `SORT_DESC`.

&nbsp;&nbsp;

**$sort_by** *string*

Optional, default is `post_modified`. Sort by `post_modified` or `post_date`.

&nbsp;&nbsp;
