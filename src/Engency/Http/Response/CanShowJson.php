<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait CanShowJson
 *
 * @package Engency\Http\Response
 */
trait CanShowJson
{

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function canShowJson(Request $request)
    {
        return $request->expectsJson();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doShowJson(): Response
    {
        return response()->json($this->getData(), $this->getHttpStatusCode());
    }

    /**
     * @return array
     */
    abstract public function getData(): array;

    /**
     * @return int
     */
    abstract protected function getHttpStatusCode(): int;
}
