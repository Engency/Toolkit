<?php
/**
 * @author    Frank Kuipers <frank@engency.com>
 * @copyright 2020 Engency
 * @since     File available since 15-01-20 15:54
 */

namespace Engency\Http\Response;

use Closure;
use Engency\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait CanShowView
 *
 * @package Engency\Http\Response
 */
trait CanShowView
{

    /**
     * @var string
     */
    private $view = 'pages.raw-data-view';
    /**
     * @var array
     */
    private $viewData = [];
    /**
     * @var array|\Closure[]
     */
    private $postponedViewData = [];

    /**
     * @param string $view
     *
     * @return $this
     */
    public function view(string $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return bool
     */
    protected function hasView() : bool
    {
        return strlen($this->view) > 0;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function canShowView(Request $request)
    {
        return !$request->expectsJson();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function doShowView() : Response
    {
        $allData  = $this->getData();
        $data     = $allData['data'];
        $instance = $this->getInstance();
        if ($instance instanceof Model) {
            $data[Str::camel(class_basename($instance))] = $instance;
        }
        $data = array_merge($allData['meta'] ?? [], $data);

        $this->calculateViewData();
        foreach ($this->viewData as $key => $value) {
            $data[$key] = $value;
        }

        return response()->view(
            strlen($this->view) > 0 ? $this->view : 'pages.raw-data-view',
            $data,
            $this->getHttpStatusCode()
        );
    }

    /**
     * @return array
     */
    abstract public function getData() : array;

    /**
     * @return \Engency\Models\Model|null
     */
    abstract protected function getInstance();

    /**
     * @return void
     */
    private function calculateViewData()
    {
        foreach ($this->postponedViewData as $closure) {
            $this->addDataForView($closure());
        }
    }

    /**
     * @param array|\Closure $data
     *
     * @return $this
     */
    public function addDataForView($data)
    {
        if ($data instanceof Closure) {
            $this->postponedViewData[] = $data;

            return $this;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->viewData[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    abstract protected function getHttpStatusCode() : int;
}
