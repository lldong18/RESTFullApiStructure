<?php
namespace ApiCreator\Exporters;

use ApiCreator\Util\DirectoryCreator;

/**
 * Export the app data into a documentation format.
 *
 * TODO add details
 */
class JsonApiDoc extends AbstractExporter
{
    const DEFAULT_FILENAME = 'api-json.js';

    /** @var string directory to export to */
    protected $exportDir;

    /** @var string filename to export to */
    protected $filename;

    /** @var bool whether or not to export the file wrapped in a define (AMD) */
    protected $amd;

    /** @var string root controller, has a special url '/' */
    protected $rootController;

    /**
     * {@inheritdoc}
     */
    protected function validateSetup()
    {
        if (!$this->exportDir) {
            throw new \InvalidArgumentException('Missing exportDir');
        }

        if (!$this->filename) {
            $this->filename = self::DEFAULT_FILENAME;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        if (!DirectoryCreator::create($this->exportDir)) {
            throw new \RuntimeException("Unable to create dir: {$this->exportDir}");
        }

        $api = array(
            'api' => array(),
            'types' => array(),
        );

        if (isset($this->api['controllers'])) {
            foreach ($this->api['controllers'] as $controller) {
                $api['api'][] = $this->extractControllerData($controller);
            }
        }

        $fileToWrite = $this->exportDir . DIRECTORY_SEPARATOR . $this->filename;
        if (file_put_contents($fileToWrite, $this->generateOutput($api)) === false) {
            throw new \RuntimeException(sprintf("Unable to write file: %s", $fileToWrite));
        }
    }

    private function classNameToBaseUrl($className)
    {
        // className is something like:
        // Controllers\Account\PhotosController
        $baseUrl = $className;

        // remove the original namespace
        // ie. \Account\PhotosController
        $baseUrl = str_replace($this->api['namespace'], '', $baseUrl);

        // special case
        // this is the base of the api
        if (ltrim($baseUrl, '\\') === $this->rootController) {
            return '';
        }

        // change namespace separators to directory separators
        // ie. /Account/PhotosController
        $baseUrl = str_replace('\\', '/', $baseUrl);

        // remove "Controller" postfix
        // ie. /Account/Photos
        $baseUrl = preg_replace('/Controller$/', '', $baseUrl);

        // lowercase
        // ie. /account/photos
        $baseUrl = strtolower($baseUrl);

        return $baseUrl;
    }

    private function extractControllerData($controller)
    {
        $required = array(
            'name' => '[ missing name ]',
            'description' => '[ missing description ]',
            'className' => '',
        );
        foreach ($required as $key => $value) {
            if (!isset($controller[$key])) {
                $controller[$key] = $value;
            }
        }

        $controllerData = array(
            'name' => $controller['name'],
            'description' => $controller['description'],
            'spec' => array(
                'baseUrl' => $this->classNameToBaseUrl($controller['className']),
                'methods' => array(),
            )
        );

        if (isset($controller['actions'])) {
            foreach ($controller['actions'] as $action) {
                $controllerData['spec']['methods'][] = $this->extractActionData($action);
            }
        }

        return $controllerData;
    }

    private function extractActionData($action)
    {
        $actionData = array(
            // this disabled flag is to show "Not yet available" for a method
            //'disabled' => true,
            'type' => $action['method'],
            'url' => $action['path'],
            'description' => $action['description'],

            //
            // get parameters from...
            // - the url
            // - validations
            //
            'parameters' => array_merge(
                $this->extractAssertsFromAction($action),
                $this->extractValidationsFromAction($action)
            ),

            //
            // get response data from...
            // - success responses
            // - exceptions that are handled
            //
            'responses' => array_merge(
                $this->extractSuccessResponseFromAction($action),
                $this->extractExceptionResponsesFromAction($action)
            )
        );

        return $actionData;
    }

    private function extractAssertsFromAction($action)
    {
        $asserts = array();

        if (isset($action['assert'])) {
            foreach ($action['assert'] as $assert) {
                $asserts[] = array(
                    'name' => $assert['name'],
                    'type' => $assert['regex'],
                    'required' => true,
                    // we don't collect descriptions for parameters, yet
                    'description' => 'Extracted from the url',
                );
            }
        }

        return $asserts;
    }

    private function extractValidationsFromAction($action)
    {
        $validations = array();

        if (isset($action['validate'])) {
            foreach ($action['validate'] as $validate) {
                // we don't collect descriptions for parameters, yet
                // if we have a default value, just stuff it in here for now
                $description = '';
                if ($validate['default'] !== null) {
                    $description = sprintf('Default: %s', var_export($validate['default'], 1));
                }

                $validations[] = array(
                    'name' => $validate['name'],
                    'type' => $validate['type'],
                    'required' => $validate['optional'] ? false : true,
                    'description' => $description,
                );
            }
        }

        return $validations;
    }

    private function extractSuccessResponseFromAction($action)
    {
        $responses = array();

        if (isset($action['success'])) {
            $responses[] = array(
                'statusCode' => $action['success']['code'],
                // we are not collecting descriptions for a success, yet
                'description' => 'The request was successful.',
                // return values aren't implemented yet!
                // placeholder here just in case I forget it exists
                'returnValues' => array()
            );
        }

        return $responses;
    }

    private function extractExceptionResponsesFromAction($action)
    {
        $responses = array();

        if (isset($action['exceptions'])) {
            foreach ($action['exceptions'] as $exceptionList) {
                foreach ($exceptionList as $exceptionData) {
                    $responses[] = array(
                        'statusCode' => $exceptionData['responseCode'] ?: 500,
                        'description' => $exceptionData['responseMessage'] ?: '[ no description ]',
                    );
                }
            }
        }

        return $responses;
    }

    private function generateOutput(array $api)
    {
        $output = json_encode($api);

        if ($this->amd) {
            $output = <<<AMD
define(function(){
    return $output;
});
AMD;
        }

        return $output;
    }
}
