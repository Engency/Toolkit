<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait CanRedirect
 *
 * @package Engency\Http\Response
 */
trait CanRedirect
{

    private $suggestRedirect = false;

    /**
     * @var \Illuminate\Http\RedirectResponse|null
     */
    private $redirectObject = null;

    /**
     * @return $this
     */
    public function redirectBack()
    {
        $this->suggestRedirect = true;
        $this->redirectObject  = null;

        return $this;
    }

    /**
     * @param string $routeName
     * @param array  $parameters
     *
     * @return $this
     */
    public function redirectTo(string $routeName, array $parameters = [])
    {
        $url = route($routeName, $parameters);

        return $this->redirectToUrl($url);
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function redirectToUrl(string $url)
    {
        $this->suggestRedirect = true;
        $this->redirectObject  = redirect()->to($url);

        return $this;
    }

    /**
     * @return bool
     */
    protected function canRedirect()
    {
        return !$this->hasView() || $this->suggestRedirect;
    }

    /**
     * @return bool
     */
    abstract protected function hasView() : bool;

    /**
     * @return bool
     */
    protected function redirectsBack()
    {
        return $this->suggestRedirect && $this->redirectObject === null;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function clientAllowsRedirect(Request $request) : bool
    {
        return $request->header('allow-redirects', '') !== 'false';
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doRedirect() : Response
    {
        return $this->attachErrors($this->getBasicRedirect());
    }

    /**
     * @param RedirectResponse $redirectResponse
     *
     * @return RedirectResponse
     */
    private function attachErrors(RedirectResponse $redirectResponse) : RedirectResponse
    {
        [
            $type,
            $message,
        ] = $this->getError();

        if (strlen($message) > 0) {
            $redirectResponse->withErrors($message, 'notice-' . $type);
        }

        return $redirectResponse;
    }

    /**
     * @return array
     */
    private function getError() : array
    {
        $data     = $this->getData()['meta'];
        $hasError = isset($data['messageType']) && isset($data['message']);

        return [
            $hasError ? $data['messageType'] : null,
            $hasError ? $data['message'] : null,
        ];
    }

    /**
     * @return array
     */
    abstract public function getData() : array;

    /**
     * @return RedirectResponse
     */
    private function getBasicRedirect() : RedirectResponse
    {
        return $this->redirectObject === null ? redirect()->back() : $this->redirectObject;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function returnRedirectInJson() : Response
    {
        $data               = $this->getData();
        $data['redirectTo'] = $this->redirectObject === null ? url()->previous() : $this->redirectObject->getTargetUrl();
        $statusCode         = $this->getHttpStatusCode();

        return response()->json($data, $statusCode === 200 ? 302 : $statusCode);
    }
}
