<?php
namespace Api\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiCreator\Annotations as Api;

/**
 * Signup
 *
 * @Api\Name        ("Signup")
 * @Api\Description ("Allow someone to create accounts")
 * @Api\Before      ("setCountry")
 */
class SignupController
{
    /**
     * Get a list of available countries to signup from.
     *
     * @param Application $app app instance
     *
     * @return array
     *
     * @Api\Description ("Get list of available data to signup from")
     * @Api\Method      ("get")
     * @Api\Path        ("/")
     * @Api\Bind        ("signup.list")
     * @Api\Success     (code=200)
     */
    public static function getSignupForms(Application $app)
    {

    }

    /**
     * @param int         $packageId
     * @param Application $app       app instance
     * @param Request     $request   request instance for the current request
     *
     * @return array
     *
     * @Api\Description ("Get the signup form for a country")
     * @Api\Method      ("get")
     * @Api\Path        ("/{packageId}")
     * @Api\Assert      (name="countryCode", regex="\w{2}")
     * @Api\Bind        ("signup.form")
     * @Api\Success     (code=200)
     *
     */
    public static function getSignupForm($packageId, Application $app, Request $request)
    {
        return [];
    }

    /**
     * Post the signup form
     *
     * @param int         $packageId
     * @param Application $app       app instance
     * @param Request     $request   request instance for the current request
     *
     * @return array
     *
     * @Api\Description ("Signup a user")
     * @Api\Method      ("post")
     * @Api\Path        ("/{packageId}")
     * @Api\Assert      (name="packageId", regex="\d+")
     * @Api\Bind        ("signup.create")
     * @Api\Success     (code=200)
     *
     */
    public static function postSignupForm($packageId, Application $app, Request $request)
    {
        return new JsonResponse([], 400);

    }
}
