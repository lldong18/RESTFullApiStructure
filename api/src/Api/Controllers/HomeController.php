<?php
namespace Controllers;

use Silex\Application;
use ApiCreator\Annotations as Api;

/**
 * Endpoint for the root of the api
 *
 * @Api\Name        ("Home")
 * @Api\Description ("API root")
 */
class HomeController
{
  /**
   * Root
   *
   * @return array
   *
   * @Api\Description ("Root of the api with
     a very long description
     and newlines that shouldn't break
     the generated code")
   * @Api\Method      ("get")
   * @Api\Path        ("/")
   * @Api\Bind        ("home")
   * @Api\Success     (code=200)
   */
    public static function index()
    {
        return array();
    }

  /**
   * Post to root
   *
   * @return array
   *
   * @Api\Description ("Root of the api")
   * @Api\Method      ("post")
   * @Api\Path        ("/post")
   * @Api\Bind        ("home.post")
   * @Api\Success     (code=200)
   */
    public static function posted()
    {
        return array();
    }
}
