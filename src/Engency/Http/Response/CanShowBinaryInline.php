<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * Trait CanShowBinaryInline
 *
 * @package Engency\Http\Response
 */
trait CanShowBinaryInline
{

    private $suggestShowBinaryInline = false;

    private $inlineFile = '';

    private $inlineType = 'jpg';

    private $downloadName = 'download';

    private $etag = '';

    private $forceInline = true;

    private $removeAfterSend = false;

    /**
     * @return array
     */
    abstract public function getData() : array;

    /**
     * @param string      $file
     * @param string      $type
     * @param string      $version
     *
     * @param string|null $downloadWithName
     *
     * @return $this
     */
    public function showBinaryInline(string $file, string $type, string $version, string $downloadWithName = null)
    {
        $this->inlineFile              = $file;
        $this->inlineType              = $type;
        $this->suggestShowBinaryInline = true;
        $this->etag                    = $version;
        $this->forceInline             = $downloadWithName === null;
        $this->downloadName            = $downloadWithName;

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function removeAfterSend(bool $value = true)
    {
        $this->removeAfterSend = $value;

        return $this;
    }

    /**
     * @return int
     */
    abstract protected function getHttpStatusCode() : int;

    /**
     * @return bool
     */
    protected function canShowBinaryInline() : bool
    {
        return $this->suggestShowBinaryInline;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doShowBinaryInline() : Response
    {
        if ($this->forceInline) {
            return response()
                ->file($this->inlineFile, $this->getShowBinaryHeaders())
                ->deleteFileAfterSend($this->removeAfterSend);
        } else {
            return response()
                ->download($this->inlineFile, $this->downloadName, $this->getShowBinaryHeaders())
                ->deleteFileAfterSend($this->removeAfterSend);
        }
    }

    /**
     * @return array
     */
    private function getShowBinaryHeaders() : array
    {
        switch ($this->downloadType) {
            case 'jpg':
                return $this->getInlineHeadersForJpgType();
            default:
                return [];
        }
    }

    /**
     * @return array
     */
    private function getInlineHeadersForJpgType() : array
    {
        return [
            'Content-Type' => 'image/jpg',
            'etag'         => $this->etag,
        ];
    }
}
