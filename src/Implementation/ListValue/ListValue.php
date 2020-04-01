<?php

declare(strict_types=1);

namespace ADS\ValueObjects\Implementation\ListValue;

use ADS\ValueObjects\Exception\InvalidListException;
use ADS\ValueObjects\ValueObject;
use EventEngine\Data\ImmutableRecord;
use ReflectionClass;
use ReflectionException;
use function array_diff;
use function array_map;
use function array_pop;
use function array_push;
use function count;
use function get_class;
use function gettype;
use function is_array;
use function is_scalar;
use function print_r;

abstract class ListValue implements \ADS\ValueObjects\ListValue
{
    /** @var mixed[] */
    protected array $value;

    /**
     * @param array<mixed> $values
     */
    protected function __construct(array $values)
    {
        $this->value = $values;
    }

    /**
     * @return class-string|null
     *
     * @phpcsSuppress SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedMethod
     */
    private static function __itemType() : ?string
    {
        return static::itemType();
    }

    /**
     * @inheritDoc
     */
    public static function fromScalarToItem($value)
    {
        $itemType = static::itemType();

        try {
            $relfectionClass = new ReflectionClass($itemType);

            if ($relfectionClass->implementsInterface(ImmutableRecord::class)) {
                return $itemType::fromArray($value);
            }

            throw InvalidListException::fromScalarToItemNotImplemented(static::class);
        } catch (ReflectionException $exception) {
            throw InvalidListException::itemTypeNotFound($itemType, static::class);
        }
    }

    /**
     * @inheritDoc
     */
    public static function fromItemToScalar($item)
    {
        $itemType = static::itemType();

        try {
            $relfectionClass = new ReflectionClass($itemType);

            if ($relfectionClass->implementsInterface(ImmutableRecord::class)) {
                return $item->toArray();
            }

            throw InvalidListException::fromItemToScalarNotImplemented(static::class);
        } catch (ReflectionException $exception) {
            throw InvalidListException::itemTypeNotFound($itemType, static::class);
        }
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $value)
    {
        return static::fromItems(array_map(
            static fn ($array) => static::fromScalarToItem($array),
            $value
        ));
    }

    /**
     * @inheritDoc
     */
    public static function fromItems(array $values)
    {
        self::checkTypes($values);

        return new static($values);
    }

    /**
     * @inheritDoc
     */
    public static function emptyList()
    {
        return new static();
    }

    /**
     * @return array<mixed>
     */
    public function toArray() : array
    {
        return array_map(
            static fn($item) => static::fromItemToScalar($item),
            $this->value
        );
    }

    /**
     * @return array<mixed>
     */
    public function toItems() : array
    {
        return $this->value;
    }

    public function __toString() : string
    {
        return print_r($this->toArray(), true);
    }

    /**
     * @inheritDoc
     */
    public function isEqualTo($other) : bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return empty(
            array_diff(
                $this->toArray(),
                $other->toArray()
            )
        )
            && empty(
                array_diff(
                    $other->toArray(),
                    $this->toArray()
                )
            );
    }

    /**
     * @inheritDoc
     */
    public function push($item)
    {
        $clone = clone $this;

        array_push($clone->value, static::toItem($item));

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function put(string $key, $item)
    {
        $clone = clone $this;

        $clone->value[$key] = static::toItem($item);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function pop()
    {
        $clone = clone $this;

        array_pop($clone->value);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function forget(string $key)
    {
        $clone = clone $this;

        unset($clone->value[$key]);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, $default = null)
    {
        return $this->value[$key] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function contains($item) : bool
    {
        $item = static::toItem($item);

        foreach ($this->toItems() as $existingItem) {
            if ($existingItem instanceof ValueObject && $existingItem->isEqualTo($item)) {
                return true;
            }

            if ($existingItem === $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function first()
    {
        return $this->value[0] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function last()
    {
        return $this->value[$this->count() - 1] ?? null;
    }

    public function count() : int
    {
        return count($this->value);
    }

    public function isEmpty() : bool
    {
        return empty($this->value);
    }

    /**
     * @param mixed $values
     */
    private static function checkTypes($values) : void
    {
        $type = static::itemType();

        foreach ($values as $item) {
            if (! $item instanceof $type) {
                $givenType = is_scalar($item) ? gettype($item) : (is_array($item) ? 'array' : get_class($item));

                throw InvalidListException::noValidItemType(
                    $givenType,
                    $type,
                    static::class
                );
            }
        }
    }

    /**
     * @param mixed $item
     *
     * @return mixed
     */
    private static function toItem($item)
    {
        try {
            self::checkTypes([$item]);
        } catch (InvalidListException $exception) {
            $item = static::fromScalarToItem($item);
        }

        return $item;
    }
}
