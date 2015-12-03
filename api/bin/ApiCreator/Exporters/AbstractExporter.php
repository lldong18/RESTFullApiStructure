<?php
namespace ApiCreator\Exporters;

/**
 * Base Exporter class to provide some common functionality.
 */
abstract class AbstractExporter
{
    /** @var array representation of the api from ApiCreator\Reader */
    protected $api;

    /**
     * Set options and check if we can continue
     *
     * @param array $api         Normalized API representation
     * @param array $options Options for this exporter
     */
    public function __construct(array $api, array $options = array())
    {
        $this->api = $api;
        $this->setOptions($options);
        $this->validateSetup();
    }


    /**
     * Set options if the class property is defined
     *
     * @param array $options
     */
    protected function setOptions($options)
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Check if we have enough to run this exporter
     *
     * If we want to stop the exporter, throw an exception
     *
     * @throws \Exception
     */
    abstract protected function validateSetup();

    /**
     * Perform the export!
     */
    abstract public function export();
}
