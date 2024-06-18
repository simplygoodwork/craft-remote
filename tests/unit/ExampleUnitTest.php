<?php
/**
 * Telemetron plugin for Craft CMS 3.x
 *
 * An internal project tracking tool.
 *
 * @link      https://simplygoodwork.com
 * @copyright Copyright (c) 2022 Good Work
 */

namespace simplygoodwork\telemetrontests\unit;

use Codeception\Test\Unit;
use UnitTester;
use Craft;
use simplygoodwork\telemetron\Remote;

/**
 * ExampleUnitTest
 *
 *
 * @author    Good Work
 * @package   Telemetron
 * @since     1.0.0
 */
class ExampleUnitTest extends Unit
{
    // Properties
    // =========================================================================

    /**
     * @var UnitTester
     */
    protected $tester;

    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    /**
     *
     */
    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            Remote::class,
            Remote::$plugin
        );
    }

    /**
     *
     */
    public function testCraftEdition()
    {
        Craft::$app->setEdition(Craft::Pro);

        $this->assertSame(
            Craft::Pro,
            Craft::$app->getEdition()
        );
    }
}
