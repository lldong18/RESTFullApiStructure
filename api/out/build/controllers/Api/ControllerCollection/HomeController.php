<?php
namespace Api\ControllerCollection;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

use Wsbox\Validator\Exception\CollectionValidatorException;
use Wsbox\Assert\Exception\AssertException;

/**
 * Name:        Home
 * Path:        /
 * Description: API root
 */
class HomeController implements ControllerProviderInterface
{
  /**
   * Return routes required by ControllerProviderInterface
   *
   * @param Application $app
   *
   * @return ControllerCollection
   */
  public function connect(Application $app) {
    $controllers = $app['controllers_factory'];


    // controller level exception handler
    $exceptionHandler = function($exception) use ($app) {
      // CollectionValidatorException will be handled automatically for ALL controllers
      if ($exception instanceof CollectionValidatorException) {
        throw new HttpException($exception->getCode(), $exception->getMessage(), $exception);
      }
      if ($exception instanceof AssertException) {
        // rethrow this exception to let the app handle it
        throw $exception;
      }
      // 1. if we've made it here, just let the main exception handler handle this
      // 2. $app->abort() is just a short cut to throw an HttpException.
      //    Here we also pass in the previous exception such that we can log all errors from the main exception handler.
      throw new HttpException(500, '', $exception);
    };

    /* Description: Root of the api with
     a very long description
     and newlines that shouldn't break
     the generated code */
    // GET /
    $controllers->get('/', function(Request $request) use ($app, $exceptionHandler) {

      try {
        $parameters = array();
        $validParameters = self::validateRequestParameters($parameters, $request);

        $data = \Controllers\HomeController::index();

        if ($data instanceof Response) {
          return $data;
        }
      } catch (\Exception $exception) {
        // action level exception handler
        // controller level exception handler
        $exceptionHandler($exception);
      }

      // respond in json for now...probably shouldn't handle it from here
      $headers = array(
        'Content-Type' => 'application/json',
      );
      return new Response(json_encode($data), 200, $headers);
    })
      ->bind('home')
      ;

    /* Description: Root of the api */
    // POST /post
    $controllers->post('/post', function(Request $request) use ($app, $exceptionHandler) {

      try {
        $parameters = array();
        $validParameters = self::validateRequestParameters($parameters, $request);

        $data = \Controllers\HomeController::posted();

        if ($data instanceof Response) {
          return $data;
        }
      } catch (\Exception $exception) {
        // action level exception handler
        // controller level exception handler
        $exceptionHandler($exception);
      }

      // respond in json for now...probably shouldn't handle it from here
      $headers = array(
        'Content-Type' => 'application/json',
      );
      return new Response(json_encode($data), 200, $headers);
    })
      ->bind('home.post')
      ;

    return $controllers;
  }

  /**
   * validate parameters passed in with a request and apply defaults if
   * available
   *
   * @param array $parameters parameters to validate
   * @param Request $request  instance of the current request
   *
   * @return array
   */
  public static function validateRequestParameters(array $parameters, Request $request)
  {
    $input = array();
    $constraints = array();
    $defaults = array();
    foreach ($parameters as $name => $param) {
      // get the input
      switch ($param['type']) {
        case 'file':
          $var = $request->files->get($name);
          break;
        default:
          $var = $request->request->get($name);
      }

      // cast some variables
      if ($var !== null) {
        switch ($param['type']) {
          case 'bool':
            if (strtolower($var) == 'false') {
                $var = false;
            }
            $var = (bool)$var;
            break;
          case 'integer':
            if (preg_match('/\d+/', $var)) {
              $var = (int)$var;
            }
            break;
        }
      }

      // initialize constraints for each variable
      $constraints[$name] = array();

      // is this var required?
      if (empty($param['optional'])) {
        $constraints[$name][] = new Assert\NotBlank(array('message' => "$name is missing"));
      }

      // add constraints depending on the type
      switch ($param['type']) {
        case 'file':
          $constraints[$name][] = new Assert\File();
          break;
        case 'string':
          $constraints[$name][] = new Assert\Length(array('min' => 1));
          break;
        case 'bool':
          $constraints[$name][] = new Assert\Type(array('type' => 'bool'));
          break;
        case 'integer':
          $constraints[$name][] = new Assert\Type(array('type' => 'integer'));
          break;
        default:
          throw new \Exception("Unknown type: {$param['type']}", 500);
      }

      // get default values, if needed
      if ($var === null && array_key_exists('default', $param)) {
        $defaults[$name] = $param['default'];
      }

      $input[$name] = $var;
    }

    // set up validation collection
    $validator = Validation::createValidator();
    $violations = $validator->validateValue($input, new Assert\Collection($constraints));

    if (count($violations)) {
      throw new CollectionValidatorException($violations, 400);
    }

    // apply defaults
    // note: the default keys will overwrite the input's keys
    // Note: this is ok because keys in $defaults are not present in $input.
    $input = array_merge($input, $defaults);

    return $input;
  }
}

