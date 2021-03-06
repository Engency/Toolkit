<?php

namespace Engency\Http\Controllers;

use Engency\Http\Response\DefaultResponse;
use Engency\Http\Response\Response;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Collection;

Trait BaseResponseMethods
{
    /**
     * @param Collection|array|\Illuminate\Database\Eloquent\Model|\Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator $data
     * @param string                                                                                                                                      $view
     *
     * @return \Engency\Http\Response\Response
     */
    protected function success($data = [], string $view = '') : Response
    {
        $response = new Response($data);
        if (strlen($view) > 0) {
            $response->view($view);
        }

        return $response;
    }

    /**
     * @param int $httpErrorCode
     *
     * @return \Engency\Http\Response\Response
     */
    protected function failure(int $httpErrorCode = IlluminateResponse::HTTP_CONFLICT) : Response
    {
        if ($httpErrorCode == IlluminateResponse::HTTP_CONFLICT) {
            return DefaultResponse::unprocessable('')->redirectBack();
        }

        return ( new Response() )->httpError($httpErrorCode);
    }
}