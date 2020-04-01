<?php

declare(strict_types=1);

namespace ADS\ValueObjects;

interface ListValue extends ValueObject
{
    /**
     * @return class-string
     */
    public static function itemType() : string;

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public static function fromScalarToItem($value);

    /**
     * @param mixed $item
     *
     * @return mixed
     */
    public static function fromItemToScalar($item);

    /**
     * @param array<mixed> $value
     *
     * @return static
     */
    public static function fromArray(array $value);

    /**
     * @param array<mixed> $values
     *
     * @return static
     */
    public static function fromItems(array $values);

    /**
     * @return static
     */
    public static function emptyList();

    /**
     * @return array<mixed>
     */
    public function toArray() : array;

    /**
     * @return array<mixed>
     */
    public function toItems() : array;

    /**
     * @param mixed $item
     *
     * @return static
     */
    public function push($item);

    /**
     * @param mixed $item
     *
     * @return static
     */
    public function put(string $key, $item);

    /**
     * @return static
     */
    public function pop();

    /**
     * @return static
     */
    public function forget(string $key);

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default);

    /**
     * @param mixed $item
     */
    public function contains($item) : bool;

    /**
     * @return mixed|null
     */
    public function first();

    /**
     * @return mixed|null
     */
    public function last();

    public function count() : int;

    public function isEmpty() : bool;
}
