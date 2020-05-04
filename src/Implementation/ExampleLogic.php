<?php

declare(strict_types=1);

namespace ADS\ValueObjects\Implementation;

use ADS\ValueObjects\BoolValue;
use ADS\ValueObjects\EnumValue;
use ADS\ValueObjects\Exception\ExampleException;
use ADS\ValueObjects\FloatValue;
use ADS\ValueObjects\Implementation\Int\RangeValue;
use ADS\ValueObjects\Implementation\String\Base64EncodedStringValue;
use ADS\ValueObjects\Implementation\String\EmailValue;
use ADS\ValueObjects\Implementation\String\UrlValue;
use ADS\ValueObjects\Implementation\String\UuidValue;
use ADS\ValueObjects\IntValue;
use ADS\ValueObjects\StringValue;
use Faker\Factory;
use ReflectionClass;

trait ExampleLogic
{
    /**
     * @inheritDoc
     */
    public static function example()
    {
        $reflection = new ReflectionClass(static::class);

        switch (true) {
            case $reflection->isSubclassOf(UuidValue::class):
                return static::generate();
            case $reflection->isSubclassOf(EmailValue::class):
                return static::fromString(Factory::create()->email);
            case $reflection->isSubclassOf(UrlValue::class):
                return static::fromString(Factory::create()->url);
            case $reflection->isSubclassOf(Base64EncodedStringValue::class):
                return static::fromPlainString(Factory::create()->word());
            case $reflection->isSubclassOf(RangeValue::class):
                return static::fromInt(Factory::create()->numberBetween(static::minimum(), static::maximum()));
            case $reflection->implementsInterface(EnumValue::class):
                return static::fromValue(Factory::create()->randomElement(static::possibleValues()));
            case $reflection->implementsInterface(FloatValue::class):
                return static::fromFloat(Factory::create()->randomFloat());
            case $reflection->implementsInterface(IntValue::class):
                return static::fromInt(Factory::create()->randomNumber());
            case $reflection->implementsInterface(StringValue::class):
                return static::fromString(Factory::create()->word());
            case $reflection->implementsInterface(BoolValue::class):
                return static::fromBool(Factory::create()->boolean());
            default:
                throw ExampleException::noExampleFound(static::class);
        }
    }
}
