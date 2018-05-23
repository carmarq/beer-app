<?php


require_once dirname(__DIR__, 3) . "/vendor/autoload.php";
require_once dirname(__DIR__, 3) . "/php/classes/autoload.php";
require_once ("/etc/apache2/capstone-mysql/encrypted-config.php");
require_once dirname(__DIR__, 3) . "/php/lib/xsrf.php";
require_once dirname(__DIR__, 3) . "/php/lib/uuid.php";
require_once dirname(__DIR__, 3) . "/php/lib/jwt.php";

use Edu\Cnm\Beer\Profile;

/**
 * Cloudinary API for image upload
 *
 * @author brentTheDev | bkie3@cnm.edu
 * @version 1.01 modeled after Marty Bonacci aka BIG DADDY MUSTACHE
 * @team Fullstack PHP Cohort 20 | Gang of Four
 */


// start session
if(session_status() !==PHP_SESSION_ACTIVE) {
	session_start();
}

// prepare an empty reply
$reply = new StdClass();
$status->status = 200;
$reply->data = null;

try {

			// Grab the mySQL Connection
			$pdo = connectToEncryptedMySQL("/etc/apache2/capstone-mysql/???.ini");

			// determine which HTTP method is being used
			$method = array_key_exists("HTTP_X_HTTP_METHOD", $_SERVER) ? $_SERVER["HTTP_X_HTTP_METHOD"] : $_SERVER["REQUEST_METHOD"];

			$profileId = filter_input(INPUT_GET, "profileId", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
			$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

			$config = readConfig("/etc/apache2/capstone-mysql/???.ini");
			$cloudinary = json_decode($config["cloudinary"]);
			\Cloudinary::config(["cloud_name" => $cloudinary->cloudName, "api_key" => $cloudinary->apiKey, "api_secret" => $cloudinary->apiSecret]);

			// make sure the id is valid for methods that require it
			if(($method === "DELETE" || $method === "PUT") && (empty($id) === true)) {
				throw(new InvalidArgumentException("id cannot be empty or negative", 405));
			}
}