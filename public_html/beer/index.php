<?php

require_once dirname(__DIR__, 3) . "/vendor/autoload.php";
require_once dirname(__DIR__, 3) . "/php/classes/autoload.php";
require_once dirname(__DIR__, 3) . "/php/lib/xsrf.php";
require_once dirname(__DIR__, 3) . "/php/lib/uuid.php";
require_once("/etc/apache2/capstone-mysql/encrypted-config.php");


use Edu\Cnm\Beer\{
	Beer,
	// we only use the profile class for testing purposes
	Profile
};

/**
 * Api for the Beer Class
 *
 * @author Deep Dive Coding, Cohort 20, Gang of Four
 */

//verify session, start if not active
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

$reply = new stdClass();
$reply->status = 200;
$reply->data = null;

try {
	//grab mySQL connection
	$pdo = connectToEncryptedMySQL("/etc/apache/capstone-mysql/beer-app.ini");

	//determine which HTTP method is being used
	$method = $_SERVER["HTTP_X_HTTP_METHOD"] ?? $_SERVER["REQUEST_METHOD"];

	//sanitize input
	$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$beerProfileId === filter_input(INPUT_GET, "beerProfileId", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	// TODO get more information on what inputs are requisite for API calls

	//make sure the id is valid for methods that require it
	if(($method === "DELETE" || $method === "PUT") && (empty($id) === true)) {
		throw
		(new InvalidArgumentException("id cannot be empty", 405));
	}

	// handle GET request - if id is present, that beer is returned, otherwise all beers are returned
	if($method === "GET") {
		//set XSRF cookie
		setXsrfCookie();

		//get a specific beer or all beers and update reply
		if(empty($id) === false) {
			$reply->data = Beer::getBeerbyBeerId($pdo, $id);
		} else if(empty($beerProfileId) === false) {
			$reply->data = Beer::getBeerByBeerProfileId($pdo, $beerProfileId);
		} else if(empty($beerAbv) === false) {
			$reply->data = Beer::getBeerByBeerAbv($pdo, $beerAbv);
		} else if(empty($beerIbu) === false) {
			$reply->data = Beer::getBeerByBeerIbu($pdo, $beerIbu);
		} else if(empty($beerName) === false) {
			$reply->data = Beer::getBeerByBeerName($pdo, $beerName);
		} else {
			$reply->data = Beer::getAllBeers($pdo)->toArray();
		}
	} else if($method === "PUT" || $method === "POST") {

		//enforce that the user has an XSRF token
		verifyXsrf();

		//Retrieves and stores the JSON package from the front end.
		$requestContent = file_get_contents("php://input");

		//decode JSON package
		$requestObject = json_decode($requestContent);

		//check that required fields are available
		if(empty($requestObject->beerAbv) === true) {
			throw (new \InvalidArgumentException("One too many? Your beer has no Abv!.", 405));
		} else if(empty($requestObject->beerName) === true) {
			throw (new \InvalidArgumentException("Every brew needs a name!"));
		}

		//check optional params, if empty set to null
		if(empty($requestObject->beerDescription) === true) {
			$requestObject->beerDescription = null;
		} else if(empty($requestObject->beerIbu) === true) {
			$requestObject->beerIbu = null;
		}
	}

	// make sure profileId and beerId are available
	if(empty($requestObject->beerProfileId) === true) {
		throw(new \InvalidArgumentException("No Profile ID.", 405));
	}

	if(empty($requestObject->beerId) === true) {
		throw(new\InvalidArgumentException("No beer ID.",405));
	}

	//execute the actual PUT or POST
	if($method === "PUT") {

		//retrieve the beer to update
		$beer = Beer::getBeerbyBeerId($pdo, $id);
		if($beer === null) {
			throw(new RuntimeException("Beer does not exist", 404));
		}

		//enforce user signed in and only editing their own beer
		if(empty($_SESSION["profile"]) === true || $_SESSION["profile"]->getProfileId () !== $beer->getBeerProfileId()) {
			throw(new \InvalidArgumentException("You are not allowed to edit this beer", 403));
		}

		//update all attributes
		$beer->setBeerAbv($requestObject->beerAbv);
		$beer->setBeerDescription($requestObject->beerDescription);
		$beer->setBeerIbu($requestObject->beerIbu);
		$beer->setBeerName($requestObject->beerName);
		$beer->update($pdo);

		//update reply
		$reply->message = "Beer updated OK";
	} else if(method === "POST") {

		//enforce the user sign in
		if(empty($_SESSION["profile"]) === true) {
			throw(new \InvalidArgumentException("you must be logged in to do that", 403));
		}

		//create new beer and insert it into the database
		$beer = new Beer(generateUuidV4(), $_SESSION["profile"]->getProfileId(), $requestObject->beerAbv,
			$requestObject->beerDescription, $requestObject->beerIbu, $requestObject->beerName);
		$beer->insert($pdo);

		// update reply
		$reply->message = "Beer created successfully";

	} else if($method === "DELETE") {

		//enforce that the end user has an XSRF token.
		verifyXsrf();

		//retrieve the beer to be deleted
		$beer = Beer::getBeerbyBeerId($pdo, $id);
		if($beer === null) {
			throw(new RuntimeException("That beer doesn't exist", 404));
		}

		//enforce the user is signed in and only trying to edit their own beer
		if(empty($_SESSION["profile"]) === true || $_SESSION["profile"]->getBeerProfileId() !== $beer->getBeerProfileId
			()) {
			throw(new \InvalidArgumentException("You are not allowed to delete this beer.", 403));
		}

		//delete beer
		$beer->delete($pdo);
		//update reply
		$reply->message = "Beer succesfully deleted";
	} else {
		throw(new \InvalidArgumentException("Invalid HTTP request"));
	}

} catch(\Exception | \TypeError $exception) {
	$reply->status = $exception->getCode();
	$reply->message = $exception->getMessage();
}

//encode and return reply to front end caller
header("Content-type: application/json");
echo json_encode($reply);

