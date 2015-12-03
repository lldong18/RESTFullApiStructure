<?php
namespace ApiCreator\Tests;

use ApiCreator\Reader;

/**
 * Test for Reader
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test retrieving api array
     */
    public function testGetApi()
    {
        $reader = new Reader(__DIR__ . '/../Fixtures/Reader', 'ApiCreator\Fixtures\Reader');
        $api = $reader->getApi();

        $this->assertTrue(is_array($api));
        $this->assertArrayHasKey('controllers', $api);
        // TODO find a better way to figure out this count because if someone adds
        // another controller, they'll have to update this line too, haha
        $this->assertEquals(2, count($api['controllers']));

        // Test that it found controllers in the right namespace
        foreach ($api['controllers'] as $controller) {
            $this->assertRegExp('/ApiCreator\\\Fixtures\\\Reader/', $controller['className']);
        }

        // TODO how to test the rest of the stuff? huge array
    }
}
