<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

use Engency\Models\OccasionArrayModel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class \Engency\Http\Response\Response
 *
 * @package Engency\Http\Response
 */
class Response implements Responsable
{

    use CanRedirect, CanShowView, CanShowJson, CanDownload, CanShowBinaryInline;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Collection|null
     */
    private $collectionData = null;

    /**
     * @var LengthAwarePaginator|null
     */
    private $paginatorData = null;

    /**
     * @var array
     */
    private $responseMeta = [];

    /**
     * @var \Engency\Models\Model
     */
    private $instance;

    /**
     * @var string
     */
    private $occasion = 'default';

    /**
     * @var bool
     */
    private $success = true;

    /**
     * @var int
     */
    private $httpStatusCode = 200;

    /**
     * @param array|Model|Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator $data
     */
    public function __construct($data = [])
    {
        $this->data = [];

        if ($data instanceof Model) {
            $this->instance = $data;
        } elseif ($data instanceof Collection) {
            $this->setCollectionAsData($data);
        } elseif ($data instanceof LengthAwarePaginator) {
            $this->setLengthAwarePaginatorAsData($data);
        } else {
            $this->addData($data);
        }
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function addData(array $data)
    {
        if (isset($data['messageType']) && !in_array($data['messageType'], Notice::NOTICES)) {
            $data['messageType'] = Notice::NOTICE_INFO;
        }

        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * @param int $httpStatusCode
     *
     * @return $this
     */
    public function httpError(int $httpStatusCode)
    {
        $this->success        = false;
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }

    /**
     * @param int    $errorCode
     * @param string $errorText
     *
     * @return $this
     */
    public function error(int $errorCode, string $errorText)
    {
        $this->success       = false;
        $this->data['error'] = $errorCode;

        return $this->message($errorText);
    }

    /**
     * @param string $message
     * @param string $messageType
     *
     * @return $this
     */
    public function message(string $message, string $messageType = '')
    {
        return $this->addResponseMeta(
            [
                'message'     => $message,
                'messageType' => in_array($messageType,
                                          Notice::NOTICES) ? $messageType : $this->getExpectedNoticeType(),
            ]
        );
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function addResponseMeta(array $data)
    {
        $this->responseMeta = array_merge($this->responseMeta, $data);

        return $this;
    }

    /**
     * @return string
     */
    private function getExpectedNoticeType()
    {
        return $this->success ? Notice::NOTICE_SUCCESS : Notice::NOTICE_ERROR;
    }

    /**
     * @param string $occasion
     *
     * @return $this
     */
    public function occasion(string $occasion)
    {
        $this->occasion = $occasion;

        return $this;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return [
            'meta'       => $this->responseMeta,
            'data'       => array_merge(
                $this->data,
                $this->getInstanceData(),
                $this->getCollectionData()
            ),
            'serverTime' => time(),
            'success'    => (bool) $this->success,
        ];
    }

    /**
     * @return array
     */
    private function getInstanceData() : array
    {
        if ($this->instance === null) {
            return [];
        }

        if ($this->instance instanceof OccasionArrayModel) {
            return $this->instance->toOccasionArray($this->occasion);
        } else {
            return $this->instance->toArray();
        }
    }

    /**
     * @return array
     */
    private function getCollectionData() : array
    {
        if (!( $this->collectionData instanceof Collection )) {
            return [];
        }

        return $this->properlyExportCollection($this->collectionData);
    }

    /**
     * @param Collection $collection
     * @return array
     */
    private function properlyExportCollection(Collection $collection)
    {
        return $collection->mapWithKeys(function ($item, $key) {
            if ($item instanceof OccasionArrayModel) {
                return [$key => $item->toOccasionArray($this->occasion)];
            } elseif ($item instanceof Arrayable) {
                return [$key => $item->toArray()];
            } else {
                return [$key => $item];
            }
        })->toArray();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($this->canShowBinaryInline()) {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = $this->doShowBinaryInline();

            return $response;
        }

        if ($this->canDownload()) {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = $this->doDownload();

            return $response;
        }

        if (!$this->clientAllowsRedirect($request) && $this->canRedirect() && !$this->redirectsBack()) {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = $this->returnRedirectInJson();

            return $response;
        }

        if ($this->canShowJson($request)) {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = $this->doShowJson();

            return $response;
        }

        if ($this->canRedirect()) {
            /** @var \Symfony\Component\HttpFoundation\Response $response */
            $response = $this->doRedirect();

            return $response;
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $this->doShowView();

        return $response;
    }

    /**
     * @return int
     */
    protected function getHttpStatusCode() : int
    {
        return $this->httpStatusCode;
    }

    /**
     * @return \Engency\Models\Model|null
     */
    protected function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param Collection $collection
     */
    private function setCollectionAsData(Collection $collection)
    {
        $this->collectionData = $collection;
    }

    /**
     * @param LengthAwarePaginator $paginator
     */
    private function setLengthAwarePaginatorAsData(LengthAwarePaginator $paginator)
    {
        $this->collectionData = $paginator->getCollection();

        $this->addResponseMeta(
            [
                'currentPage'  => $paginator->currentPage(),
                'firstPageUrl' => $paginator->url(1),
                'from'         => $paginator->firstItem(),
                'lastPage'     => $paginator->lastPage(),
                'lastPageUrl'  => $paginator->url($paginator->lastPage()),
                'nextPageUrl'  => $paginator->nextPageUrl(),
                'path'         => $paginator->path(),
                'perPage'      => $paginator->perPage(),
                'prevPageUrl'  => $paginator->previousPageUrl(),
                'to'           => $paginator->lastItem(),
                'total'        => $paginator->total(),
            ]
        );
    }
}
