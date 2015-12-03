<?php
namespace ApiCreator\Tests\Exporters;

use ApiCreator\Exporters\JsonApiDoc;

/**
 * Tests for JsonApiDoc exporter.
 */
class JsonApiDocTest extends \PHPUnit_Framework_TestCase
{
    const TMP_DIR = '/tmp/tests/exporters/json-api-doc/';

    /**
     * Test exceptions are thrown with invalid arguments
     *
     * @param array $options exporter options
     *
     * @dataProvider badOptionsProvider
     */
    public function testRequiresExportDir($options)
    {
        try {
            new JsonApiDoc(array(), $options);
        } catch (\Exception $e) {
            return;
        }

        $this->fail('Should have thrown exception');
    }

    /**
     * Options that throw exceptions on instantiation
     *
     * @return array
     */
    public function badOptionsProvider()
    {
        return array(
            array(array('exportDir' => false, 'filename' => false)),
        );
    }

    /**
     * Test to ensure a file is written.
     *
     * @param array    $options        options to pass into the exporter
     * @param string $actualFile the actual filename that should be generated
     *
     * @dataProvider goodOptionsProvider
     */
    public function testExportFile($options, $actualFile)
    {
        $this->unlinkIfExists($actualFile);

        $exporter = new JsonApiDoc(array(), $options);
        $exporter->export();
        $this->assertTrue(file_exists($actualFile));
        $contents = file_get_contents($actualFile);
        $this->assertNotNull(json_decode($contents));
    }

    /**
     * Valid exporter options
     *
     * @return array
     */
    public function goodOptionsProvider()
    {
        return array(
            array(
                array('exportDir' => self::TMP_DIR, 'filename' => false),
                self::TMP_DIR . JsonApiDoc::DEFAULT_FILENAME
            ),
            array(array('exportDir' => self::TMP_DIR, 'filename' => 'my-name.js'), self::TMP_DIR . 'my-name.js'),
        );
    }

    /**
     * Test AMD option
     */
    public function testAMDOption()
    {
        $filename = 'amd-test.js';
        $file = self::TMP_DIR . $filename;
        $this->unlinkIfExists($file);

        $exporter = new JsonApiDoc(
            array(),
            array(
                'exportDir' => self::TMP_DIR,
                'filename' => $filename,
                'amd' => true
            )
        );

        $exporter->export();
        $this->assertTrue(file_exists($file));
        $contents = file_get_contents($file);
        $this->assertNull(json_decode($contents));
        $this->assertRegExp('/define/', $contents);
    }

    /**
     * Remove a file if it exists
     *
     * @param string $file
     */
    protected function unlinkIfExists($file)
    {
        if (file_exists($file)) {
            if (!unlink($file)) {
                $this->fail(sprintf('Unable to unlink: %s', $file));
            }
        }
    }
}
