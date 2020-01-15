<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * Trait CanDownload
 *
 * @package Engency\Http\Response
 */
trait CanDownload
{

    private $suggestDownload = false;

    private $downloadType = 'json';

    private $downloadName = 'download';

    /**
     * @param string $type
     * @param string $name
     *
     * @return $this
     */
    public function downloadAs(string $type = 'json', string $name = 'download')
    {
        $this->downloadType    = $type;
        $this->downloadName    = $name;
        $this->suggestDownload = true;

        return $this;
    }

    /**
     * @return bool
     */
    protected function canDownload() : bool
    {
        return $this->suggestDownload;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doDownload() : Response
    {
        return response(json_encode($this->getData()['data']), $this->getHttpStatusCode(), $this->getHeaders());
    }

    /**
     * @return array
     */
    abstract public function getData() : array;

    /**
     * @return int
     */
    abstract protected function getHttpStatusCode() : int;

    /**
     * @return array
     */
    private function getHeaders() : array
    {
        return $this->getHeadersForJsonType();
    }

    /**
     * @return array
     */
    private function getHeadersForJsonType() : array
    {
        return [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $this->downloadName . '.json"',
        ];
    }
}
