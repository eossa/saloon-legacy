<?php

namespace Saloon;

final class MockConfig
{
    /**
     * Default fixture path
     *
     * @var string
     */
    private static $fixturePath = 'tests/Fixtures/Saloon';

    /**
     * Denotes if an exception should be thrown if a fixture is missing.
     *
     * @var bool
     */
    private static $throwOnMissingFixtures = false;

    /**
     * Set the fixture path
     *
     * @param string $path
     *
     * @return void
     */
    public static function setFixturePath($path)
    {
        self::$fixturePath = $path;
    }

    /**
     * Throw an exception if a fixture doesn't exist instead of recording it.
     *
     * @return void
     */
    public static function throwOnMissingFixtures()
    {
        self::$throwOnMissingFixtures = true;
    }

    /**
     * Return the fixture path
     *
     * @return string
     */
    public static function getFixturePath()
    {
        return self::$fixturePath;
    }

    /**
     * Should we throw an exception if a fixture is missing?
     *
     * @return bool
     */
    public static function isThrowingOnMissingFixtures()
    {
        return self::$throwOnMissingFixtures;
    }
}
