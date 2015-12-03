<?php
namespace ApiCreator\Fixtures\Reader\AFolder;

use ApiCreator\Annotations as Api;

/**
 * A test controller with a sub namespace.
 *
 * @Api\Name                ("Another Test")
 * @Api\Description ("Another test controller")
 */
class AnotherController
{
    /**
     * @Api\Description ("Another Test action")
     * @Api\Method            ("post")
     * @Api\Path                ("/another")
     */
    public static function testAction()
    {
    }
}
