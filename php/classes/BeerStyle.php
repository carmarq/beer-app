<?php
namespace Edu\Cnm\Beer;

require_once("autoload.php");
require_once(dirname(__DIR__, 2) . "/classes/autoload.php");

use Ramsey\Uuid\Uuid;

class beerstyle implements \JsonSerializable {
	use ValidateUuid;
	/**
	 * Beer id for the BeerStyle
	 * @var Uuid|string $beerStyleBeerId
	 */
	private $beerStyleBeerId;
	/**
	 * Style id for the BeerStyle
	 * @var $beerStyleStyleId
	 */
	private $beerStyleStyleId;

//todo CHANGE styleId to int
	/**
	 * constructor for this BeerStyle
	 *
	 * @param Uuid $newBeerStyleBeerId beer id of the BeerStyle
	 * @param int $newBeerStyleStyleId style id of the BeerStyle
	 * @throws \InvalidArgumentException if data types are not valid
	 * @throws \RangeException if data values are out of bounds (e.g., strings too long, negative integers)
	 * @throws \TypeError if data types violate type hints
	 * @throws \Exception if some other exception occurs
	 * @Documentation https://php.net/manual/en/language.oop5.decon.php
	 **/

	public function __construct(Uuid $newBeerStyleBeerId, int $newBeerStyleStyleId) {
		try {
			$this->setBeerStyleBeerId($newBeerStyleBeerId);
			$this->setBeerStyleStyleId($newBeerStyleStyleId);
		} catch(\InvalidArgumentException | \RangeException | \Exception | \TypeError $exception) {
			$exceptionType = get_class($exception);
			throw(new $exceptionType($exception->getMessage(), 0, $exception));
		}
	}

	/**
	 * accessor method for BeerStyle beer id
	 *
	 * @return Uuid of BeerStyle beer id
	 */
	public function getBeerStyleBeerId(): Uuid {
		return ($this->beerStyleBeerId);
	}

	/**
	 * mutator method for BeerStyle beer id
	 *
	 * @param Uuid|string $newBeerStyleBeerId
	 */
	public function setBeerStyleBeerId($newBeerStyleBeerId) {
		try {
			$uuid = self::validateUuid($newBeerStyleBeerId);
		} catch(\InvalidArgumentException | \RangeException | \Exception | \TypeError $exception) {
			$exceptionType = get_class($exception);
			throw(new $exceptionType($exception->getMessage(), 0, $exception));
		}
		$this->beerStyleBeerId = $uuid;
	}


	/**
	 * accessor method for BeerStyle style id
	 *
	 * @return Uuid of BeerStyle style id
	 */
	public function getBeerStyleStyleId(): Uuid {
		return ($this->beerStyleStyleId);
	}

	/*
	 * mutator method for BeerStyle style id
	 *
	 * @param int $newBeerStyleStyleId
	 */
	public function setBeerStyleStyleId(int $newBeerStyleStyleId) {
		$newBeerStyleStyleId = filter_var($newBeerStyleStyleId, FILTER_VALIDATE_INT, FILTER_SANITIZE_NUMBER_INT);
		if($newBeerStyleStyleId < 0 || $newBeerStyleStyleId > 255) {
			throw(new \RangeException("There are no beers with this ID"));
		}

		$this->beerStyleStyleId = $newBeerStyleStyleId;
	}

	/**
	 *inserts BeerStyle style id into mySQL
	 *
	 * @param \PDO $pdo PDO connection object
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function insert(\PDO $pdo): void {

		//create query template
		$query = "INSERT INTO beerstyle (beerStyleBeerId, beerStyleStyleId) VALUES (:beerStyleBeerId, :beerStyleStyleId)";
		$statement = $pdo->prepare($query);

		//bind the member variables to the place holders in the template
		$parameters = ["beerStyleBeerId" => $this->beerStyleBeerId->getBytes(), "beerStyleStyleType" => $this->beerStyleStyleId->getBytes()];
		$statement->execute($parameters);
	}

	/**
	 * deletes this BeerStyle from mySQL
	 *
	 * @param \PDO $pdo connection object
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function delete(\PDO $pdo): void {
		//create query template
		$query = "DELETE FROM beerstyle WHERE beerStyleBeerId = :beerStyleBeerId AND beerStyleStyleId = :beerStyleStyleId";
		$statement = $pdo->prepare($query);

		//bind the member variables to the place holders in the template
		$parameters = ["beerStyleBeerId" => $this->beerStyleBeerId->getBytes(), "beerStyleStyleId" => $this->beerStyleStyleId->getBytes()];
		$statement->execute($parameters);
	}

	//TODO getByBeerStyleAndBeer getByBeerStyle getByBeer

	/**
	 * formats the state variable for JSON serialization
	 *
	 * @return array resulting state variables to serialize
	 */
	public function jsonSerialize(): array {
		$fields = get_object_vars($this);

		$fields["beerStyleBeerId"] = $this->beerStyleBeerId;
		$fields["beerStyleStyleId"] = $this->beerStyleStyleId->toString;
		return ($fields);
	}

}


