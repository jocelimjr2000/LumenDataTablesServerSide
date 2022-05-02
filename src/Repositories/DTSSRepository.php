<?php

/**
 * Doc
 * 
 * https://datatables.net/manual/server-side
 * https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/ssp.class.php
 * 
 */

namespace JocelimJr\LumenDTSS\Repositories;

use JocelimJr\LumenDTSS\Interfaces\DTSSRepositoryInterface;
use JocelimJr\LumenDTSS\Exceptions\ColumnNotFoundException;
use JocelimJR\LumenDTSS\Exceptions\InvalidModelException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DTSSRepository implements DTSSRepositoryInterface
{
    /**
     * Request
     */
    private $requestDraw = null;
    private $requestLimit = null;
    private $requestStart = null;
    private $requestSearch = null;
    private $requestOrder = null;
    private $requestColumns = null;
    private $requestModelClass = null;

    /** 
     * Conf
     */
    private $confColumns = null;

    /**
     * Result
     */
    private $resultRecordsTotal = null;
    private $resultRecordsFiltered = null;
    private $resultData = null;

    /**
     * simple
     *
     * @param  mixed $request
     * @param  mixed $modelClass
     * @param  mixed $confColumns
     * @param  mixed $extraWhere
     * @return array
     */
    public function simple(Request $request, string $modelClass, array $confColumns, array $extraWhere = []): array
    {
        if (!is_subclass_of($modelClass, 'Illuminate\Database\Eloquent\Model')) {
            throw new InvalidModelException($modelClass);
        }

        /**
         * Load reveived data
         */
        $this->__loadRequestData($request, $modelClass, $confColumns);

        $this->resultRecordsTotal = $modelClass::count();
        $this->resultRecordsFiltered = $this->resultRecordsTotal;

        /**
         * Mount query
         */
        $q = $this->__search();

        if(count($extraWhere) > 0){
            foreach ($extraWhere as $k => $v) {
                $q->where($k, $v);
            }

            $this->resultRecordsTotal = $q->count();
            $this->resultRecordsFiltered = $this->resultRecordsTotal;
        }

        /**
         * Order columns
         */
        if (is_array($this->requestOrder)) {
            $this->__order($q);
        }

        /**
         * Filter by column
         */
        if (is_array($this->requestColumns)) {
            $this->__columns($q);
        }

        $this->resultData = $q->get();

        return $this->__result();
    }

    /**
     * byQueryBuilder
     *
     * @param  mixed $request
     * @param  mixed $modelClass
     * @param  mixed $confColumns
     * @return array
     */
    public function byQueryBuilder(Request $request, Builder $modelClass, array $confColumns): array
    {
        /**
         * Load reveived data
         */
        $this->__loadRequestData($request, $modelClass, $confColumns);
        
        $this->resultRecordsTotal = $modelClass->count();
        $this->resultRecordsFiltered = $this->resultRecordsTotal;

        /**
         * Mount query
         */
        $q = $this->__search();

        /**
         * Order columns
         */
        if (is_array($this->requestOrder)) {
            $this->__order($q);
        }

        /**
         * Filter by column
         */
        if (is_array($this->requestColumns)) {
            $this->__columns($q);
        }

        $this->resultData = $q->get();

        return $this->__result();
    }
    
    /**
     * __loadRequestData
     *
     * @return void
     */
    private function __loadRequestData(Request $request, Builder|string $modelClass, array $confColumns)
    {
        $this->requestDraw = $request->draw ?: $request->input('draw');
        $this->requestLimit = $request->length ?: $request->input('length');
        $this->requestStart = $request->start ?: $request->input('start');
        $this->requestSearch = isset($request->search['value']) ? $request->search['value'] : $request->input('search.value');
        $this->requestOrder = $request->order ?: $request->input('order');
        $this->requestColumns = $request->columns ?: $request->input('columns');

        $this->requestModelClass = $modelClass;

        $this->confColumns = $confColumns;
    }
    
    /**
     * __search
     *
     * @param  mixed $query
     * @return object
     */
    private function __search(): object
    {
        $q = null;

        if (empty($this->requestSearch)) {
            $q = $this->requestModelClass->offset($this->requestStart)->limit($this->requestLimit);
        } else {

            $q = $this->requestModelClass->where(function ($query) {

                $first = true;
                foreach ($this->confColumns as $c) {
                    if (isset($c['searchable']) && $c['searchable'] == false) {
                        continue;
                    }

                    if ($first) {
                        $query->where($c['name'], 'LIKE', "%" . $this->requestSearch . "%");
                        $first = false;
                    } else {
                        $query->orWhere($c['name'], 'LIKE', "%" . $this->requestSearch . "%");
                    }
                }
            });

            $this->resultRecordsFiltered = $q->count();

            $q->offset($this->requestStart)->limit($this->requestLimit);
        }

        return $q;
    }

    /**
     * __order
     *
     * @param  mixed $query
     * @return void
     */
    private function __order(&$query)
    {
        foreach ($this->requestOrder as $v) {

            if (!isset($v['column']) || empty($v['column'])) {
                continue;
            }

            if (!isset($this->confColumns[$v['column']]['name'])) {
                throw new ColumnNotFoundException($v['column']);
            }

            $query->orderBy($this->confColumns[$v['column']]['name'], $v['dir']);
        }
    }

    /**
     * __columns
     *
     * @param  mixed $query
     * @return void
     */
    private function __columns(&$query)
    {
        foreach ($this->requestColumns as $v) {
            if (
                !isset($v['name']) || empty($v['name']) ||
                !isset($v['search']['value']) || empty($v['search']['value'])
            ) {
                continue;
            }

            if (!isset($this->confColumns[$v['name']]['name'])) {
                throw new ColumnNotFoundException($v['name']);
            }

            $query->where($this->confColumns[$v['name']]['name'], 'LIKE', "%" . $v['search']['value'] . "%");
        }

        $this->resultRecordsTotal = $query->count();
        $this->resultRecordsFiltered = $this->resultRecordsTotal;
    }
    
    /**
     * __result
     *
     * @return array
     */
    private function __result(): array
    {
        return [
            'draw' => $this->requestDraw,
            'recordsTotal' => intval($this->resultRecordsTotal),
            'recordsFiltered' => intval($this->resultRecordsFiltered),
            'data' => $this->resultData
        ];
    }
}
