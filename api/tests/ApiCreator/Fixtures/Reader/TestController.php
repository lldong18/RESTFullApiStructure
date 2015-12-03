<?php
namespace ApiCreator\Fixtures\Reader;

use ApiCreator\Annotations as Api;

/**
 * Test controller to be used with unittests.
 *
 * @Api\Name                ("Test")
 * @Api\Description ("A test controller")
 */
class TestController
{
    /**
     * @Api\Description ("Test action")
     * @Api\Method            ("get")
     * @Api\Path                ("/")
     */
    public static function testAction()
    {
    }
}
