<?php

namespace JocelimJr\LumenDTSS\Repositories;

use JocelimJr\LumenDTSS\Interfaces\DTSSRepositoryInterface;
use JocelimJR\LumenDTSS\Exceptions\InvalidModelException;
use Illuminate\Http\Request;

class DTSSRepository implements DTSSRepositoryInterface
{
    
    /**
     * simple
     *
     * @param  mixed $request
     * @param  mixed $modelClass
     * @param  mixed $columns
     * @param  mixed $extraWhere
     * @return array
     */
    public function simple(Request $request, string $modelClass, array $columns, array $extraWhere = []): array
    {
        if (!is_subclass_of($modelClass, 'Illuminate\Database\Eloquent\Model')) {
            throw new InvalidModelException($modelClass);
        }

        // Data received
        $draw = $request->draw ?: $request->input('draw');
        $limit = $request->length ?: $request->input('length');
        $start = $request->start ?: $request->input('start');
        $search = isset($request->search['value']) ? $request->search['value'] : $request->input('search.value');
        $order = $request->order ?: $request->input('order');
        $columnsPosition = $request->columnsPosition ?: $request->input('columnsPosition');

        if(is_array($columnsPosition) && count($columnsPosition) > 0){
            $newOrder = [];
            foreach($order as $k => $o){
                $_to = $o;
                if(isset($columnsPosition[$o['column']])){
                    $_to['column'] = $columnsPosition[$o['column']];
                }

                $newOrder[] = $_to;
            }

            $order = $newOrder;
        } 
        
        $recordsTotal = $modelClass::count();
        $recordsFiltered = $recordsTotal;

        $q = null;

        if(empty($search)){
            $q = $modelClass::offset($start)->limit($limit);
        }

        else{

            $q = $modelClass::where(function($query) use ($columns, $search) {

                $first = true;
                foreach($columns as $c){
                    if(isset($c['searchable']) && $c['searchable'] == false){
                        continue;
                    }
                    
                    if($first){
                        $query->where($c['name'], 'LIKE', "%{$search}%");
                        $first = false;
                    }else{
                        $query->orWhere($c['name'], 'LIKE', "%{$search}%");
                    }
                }
            });

            $recordsFiltered = $q->count();

            $q->offset($start)->limit($limit);
        }

        foreach($extraWhere as $k => $v){
            $q->where($k, $v);
        }
        
        if(is_array($order)){
            foreach($order as $v){
                $q->orderBy($columns[$v['column']]['name'], $v['dir']);
            }
        }

        $data = $q->get();

        $json_data = [
            'draw' => $draw,
            'recordsTotal' => intval($recordsTotal),
            'recordsFiltered' => intval($recordsFiltered),
            'data' => $data
        ];
        
        return $json_data;
    }

}
