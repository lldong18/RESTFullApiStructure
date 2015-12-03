<?php
namespace ApiCreator;

use ApiCreator\Reader;

/**
 * ApiCreator is used to read annotations in a set of controllers into a
 * normalized format so it can be exported into various other formats.
 */
class ApiCreator
{
    private $options;

    /**
     * Constructor that accepts an array of options.
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * Read in the API data and export the formats specified.
     *
     * @return array
     * Returns the internal (normalized) version of the api used by the various
     * exporters.
     */
    public function generate()
    {
        $reader = new Reader($this->options['controllersDir'], $this->options['namespace']);
        $api = $reader->getApi();

        // export
        foreach ($this->options['export'] as $exporter => $options) {
            $className = 'ApiCreator\Exporters\\' . $exporter;
            if (class_exists($className)) {
                $exporter = new $className($api, $options);
                $exporter->export();
            }
        }

        return $api;
    }
}
