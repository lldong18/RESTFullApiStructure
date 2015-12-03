<?php
namespace ApiCreator\Tests\Exporters;

use ApiCreator\Reader;
use ApiCreator\Exporters\Silex;

/**
 * Test for SilexControllerCollection exporter.
 */
class SilexTest extends \PHPUnit_Framework_TestCase
{
    const TMP_DIR = '/tmp/tests/exporters/silex/';

    /**
     * Remove the tmp dir before each test.
     */
    public function setUp()
    {
        $cmd = "rm -fr TMP_DIR";
        exec($cmd, $output, $code);
        if ($code !== 0) {
            $this->fail(sprintf('Unable to remove tmp dir: %s', $output));
        }
    }

    /**
     * Test exceptions thrown during instantiation
     *
     * @param array $options options to pass into the exporter
     *
     * @dataProvider badOptionsProvider
     */
    public function testRequiresExportDir($options)
    {
        try {
            new Silex(array(), $options);
        } catch (\Exception $e) {
            return;
        }

        $this->fail('Should have thrown exception');
    }

    /**
     * Provider for options that will throw exceptions on instantiation.
     *
     * @return array
     */
    public function badOptionsProvider()
    {
        return array(
            array(array('exportDir' => false, 'newNamespace' => 'Api\Controllers')),
            array(array('exportDir' => self::TMP_DIR, 'newNamespace' => false)),
        );
    }

    /**
     * Test that controller files get created
     *
     * TODO maybe somehow check the contents of the exported stuff
     */
    public function testFilesExported()
    {
        $namespace = 'ApiCreator\Fixtures\Reader';
        $reader = new Reader(__DIR__ . '/../../Fixtures/Reader', $namespace);
        $api = $reader->getApi();

        $options = array('exportDir' => self::TMP_DIR, 'newNamespace' => 'Api\Controllers');

        $exporter = new Silex($api, $options);
        $exporter->export();

        foreach ($api['controllers'] as $controller) {
            $class = str_replace($namespace . '\\', 'Api\Controllers\\', $controller['className']);
            $filename = self::TMP_DIR . str_replace('\\', '/', $class) . '.php';
            $this->assertTrue(file_exists($filename), 'For file: ' . $filename);
        }
    }

    /**
     * Sort
     */
    public function testSort()
    {
        $paths = array(
            '/account' => 'AccountController',
            '/profile' => 'ProfileController',
            '/' => 'HomeController',
            '/account/privatephotos' => 'Account/PhotosController',
            '/account/photos' => 'Account/PhotosController',
        );

        $expect = array(
            '/account/privatephotos' => 'Account/PhotosController',
            '/account/photos' => 'Account/PhotosController',
            '/profile' => 'ProfileController',
            '/account' => 'AccountController',
            '/' => 'HomeController',
        );

        $exporter = new Silex(
            array(),
            array(
                'exportDir' => self::TMP_DIR,
                'newNamespace' => 'Api\Controllers'
            )
        );

        $this->assertSame($expect, $exporter->sortRoutes($paths));
    }
}
