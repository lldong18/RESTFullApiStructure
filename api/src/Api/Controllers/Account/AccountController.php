<?php
namespace Controllers;

use Silex\Application;
use ApiCreator\Annotations as Api;

/**
 * Endpoint for a user's account.
 *
 * @Api\Name        ("Account")
 * @Api\Description ("A user's account")
 */
class AccountController
{
  /**
   * Get some account info.
   *
   * @return array
   *
   * @Api\Description ("Get account info")
   * @Api\Method      ("get")
   * @Api\Path        ("/")
   * @Api\Bind        ("account")
   * @Api\Success     (code=200)
   */
    public static function getAccount()
    {
        return array();
    }
}
