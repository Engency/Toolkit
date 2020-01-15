<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

use Illuminate\Http\Response as IlluminateResponse;

/**
 * Class DefaultResponse
 *
 * @package Engency\Http\Response
 */
class DefaultResponse
{

    /**
     * @param string $message
     *
     * @return \Engency\Http\Response\Response
     */
    public static function pageNotFound(string $message = 'Page not found')
    {
        return ( new Response() )
            ->httpError(IlluminateResponse::HTTP_NOT_FOUND)
            ->error(-1, $message)
            ->view('pages.error.notfound');
    }

    /**
     * @return \Engency\Http\Response\Response
     */
    public static function unauthorized()
    {
        return ( new Response() )
            ->httpError(IlluminateResponse::HTTP_UNAUTHORIZED)
            ->view('pages.error.unauthorized');
    }

    /**
     * @param string $message
     *
     * @return \Engency\Http\Response\Response
     */
    public static function forbidden(string $message = 'You don\'t have permissions to access this page.')
    {
        return ( new Response() )
            ->httpError(IlluminateResponse::HTTP_FORBIDDEN)
            ->error(-1, $message)
            ->view('pages.error.forbidden');
    }

    /**
     * @param string $message
     *
     * @return \Engency\Http\Response\Response
     */
    public static function unprocessable(string $message)
    {
        return ( new Response() )
            ->httpError(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->error(-1, $message)
            ->view('pages.error.conflict');
    }

    /**
     * @param string $message
     *
     * @return \Engency\Http\Response\Response
     */
    public static function internalError(string $message = 'An unknown error occurred.')
    {
        return ( new Response() )
            ->httpError(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)
            ->error(-1, $message)
            ->view('pages.error.500');
    }
}
