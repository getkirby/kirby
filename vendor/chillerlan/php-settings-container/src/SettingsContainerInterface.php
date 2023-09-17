<?php
/**
 * Interface SettingsContainerInterface
 *
 * @created      28.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\Settings;

use JsonSerializable;

/**
 * a generic container with magic getter and setter
 */
interface SettingsContainerInterface extends JsonSerializable{

	/**
	 * Retrieve the value of $property
	 *
	 * @return mixed|null
	 */
	public function __get(string $property);

	/**
	 * Set $property to $value while avoiding private and non-existing properties
	 *
	 * @param string $property
	 * @param mixed  $value
	 */
	public function __set(string $property, $value):void;

	/**
	 * Checks if $property is set (aka. not null), excluding private properties
	 */
	public function __isset(string $property):bool;

	/**
	 * Unsets $property while avoiding private and non-existing properties
	 */
	public function __unset(string $property):void;

	/**
	 * @see SettingsContainerInterface::toJSON()
	 */
	public function __toString():string;

	/**
	 * Returns an array representation of the settings object
	 */
	public function toArray():array;

	/**
	 * Sets properties from a given iterable
	 */
	public function fromIterable(iterable $properties):SettingsContainerInterface;

	/**
	 * Returns a JSON representation of the settings object
	 * @see \json_encode()
	 */
	public function toJSON(int $jsonOptions = null):string;

	/**
	 * Sets properties from a given JSON string
	 *
	 * @throws \Exception
	 * @throws \JsonException
	 */
	public function fromJSON(string $json):SettingsContainerInterface;

}
