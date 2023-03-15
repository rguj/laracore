<?php

namespace Rguj\Laracore\Traits\Operations;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Exception;

trait FetchOperation
{



    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Suffix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupFetchRoutes($segment, $routeName, $controller, bool $isPost = false)
    {

        if($segment !== $routeName)
            goto point1;
        
        preg_match_all('/(?<=^|;)fetch([^;]+?)(;|$)/', implode(';', get_class_methods($this)), $matches);

        $segment = Str::of($segment)->ltrim('/')->rtrim('/')->__toString();
        $segment2 = Str::replace('/', '.', $segment);

        foreach($matches[1] as $k=>$v) {
            $v2 = lcfirst($v);
            if($isPost) {
                Route::post($segment.'/fetch/'.$v2, [
                    'as'        => $segment2.'.fetch.'.lcfirst($v2),
                    'uses'      => $controller.'@fetch'.$v,
                    'operation' => 'fetch',
                ]);
            } else {
                Route::get($segment.'/fetch/'.$v2, [
                    'as'        => $segment2.'.fetch.'.lcfirst($v2),
                    'uses'      => $controller.'@fetch'.$v,
                    'operation' => 'fetch',
                ]);
            }
        }

        point1:
    }

    public function setupFetchOperation()
    {
        $this->crud->setOperation('fetch');
        // $this->crud->setOperationSetting();

    }

    /**
     * Simplified fetch function
     *
     * @param array $attr
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function fetch(array $attr)
    {
        /* CONTENTS OF $attr        
        [
            'model' => SampleModel::class, // (required) (string|closure) you can customize the model
            'searchable_attributes' => [], // (optional) but required for searchable attributes
            'paginate' => 10,              // (optional) aka "request.length", items to show per page, -1 to get all
            'searchOperator' => '',        // (disabled) rendered useless
            'query' =>                     // (disabled) use "model"
                function($model) {
                    /** @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $m *\/
                    $m = $model;
                    $search = request()->input('q') ?? false;
                    if ($search) {
                        return $m->whereRaw('CONCAT(`first_name`," ",`last_name`) LIKE "%' . $search . '%"');
                    } else {
                        return $m;
                    }
                }
            ,

            # ---------------
            # custom
            # ---------------

            'start' => 1,             // (optional) (default: 0) starts on zero
            'search' => []|"",        // (optional) (default: "") the keyword to search
            'order' => [],            // (optional) (default: []) set the order ["key"=>"asc|desc"]

            'columns' => [            // (required)
                [  // do this for each attribute
                    attr          (required) - column unique name
                    db            (required) - db table column
                    label         (required) - the display title in datatable
                    db_fake       (disabled) - ???
                    dt            (optional) - datatable sequence #
                    class         (optional) - css classes
                    sortable      (optional) - if column is sortable       (default: false)
                    searchable    (optional) - if column is searchable     (default: false)
                    type          (optional) - server-side column type     (default: "string")
                    frontend_type (optional) - frontend column type        (default: "string") 
                                                (date, num, num-fmt, html-num, html-num-fmt, html, string)
                                                https://datatables.net/reference/option/columns.type
                    formatter     (optional) - formats value: function($value) { // your code }      (default: null)
                    same_as       (optional) - copy the precedent column's characteristics           (default: "")
                ],
            ],

            'draw' => 1,              // (disabled) (default: 1) draw requests
            '_' => 1,                 // (disabled) (default: "")

        ]*/


        // $this->crud->hasAccessOrFail('fetch');

        // $ses_key = 'route.'.request()->route()->getName();

        $attr['searchable_attributes'] = (array)($attr['searchable_attributes'] ?? []);


        $attr['request']['draw'] = 1;  // always 1, implement session autoincrement later
        // $attr['request']['length'] = (int)($attr['request']['length'] ?? 0);
        $attr['request']['length'] = (int)($attr['paginate'] ?? -1);  // 0
        $attr['request']['start'] = (int)($attr['start'] ?? 0);  // start on 0
        $attr['request']['search'] = $this->morphSearchRequest($attr['search'] ?? '');
        $attr['request']['_'] = (string)($attr['_'] ?? '');
        $attr['request']['columns'] = $this->morphColumnsRequest($attr['columns'] ?? [], $attr['searchable_attributes']);  // not validated, assumes correct array structure
        $attr['request']['order'] = $this->morphOrderRequest($attr['order'] ?? [], $attr['request']['columns']);

        // dd($attr);

        $d = null;
        $m = null;
        try {
            if(
                !in_array(gettype($attr['model']), ['string', 'object'])
                || (is_string($attr['model']) && !class_exists($attr['model']))
                || !(
                    (is_object($attr['model']) && in_array($attr['model']::class, ['Illuminate\Database\Eloquent\Builder', 'Illuminate\Database\Query\Builder'], true))
                    || array_key_exists('Illuminate\Database\Eloquent\Model', class_parents($attr['model']))
                )
            )
                throw new exception('Invalid class');  // throw new exception('Class doesn\'t exists');

            /** @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $m */
            $m = is_string($attr['model']) ? (new $attr['model']()) : $attr['model'];
            // dd($m);

            // dd($m->get());
            // dd($m->toSql());

            // invoke query closure
            // if(is_callable($attr['query'])) {
            //     $m = $attr['query']($m);
            // }
            
            /** @var \Rguj\Laracore\Request\Request $r */
            $r = resolve(\Rguj\Laracore\Request\Request::class);
            $r->query->set('draw', $attr['request']['draw']);
            $r->query->set('length', $attr['request']['length']);
            $r->query->set('start', $attr['request']['start']);
            $r->query->set('search', $attr['request']['search']);
            $r->query->set('_', $attr['request']['_']);
            $r->query->set('columns', $attr['request']['columns']);
            $r->query->set('order', $attr['request']['order']);
           
            $d = datatable_paginate($r, $attr['request']['columns'], $m, false, false);

        } catch(\Exception $ex) {
            // dd($ex);
            $d = [];
            // throw new exception($ex->getMessage());
        }

        // dd($d);
        return $d;
    }





    public function morphColumnsRequest(array $columns, array $searchable_attributes)
    {
        $cols = [];
        // traverse columns for some correction
        foreach($columns as $k=>$v) {
            $cols[$k] = $v;
            $cols[$k]['searchable'] = in_array($v['attr'], $searchable_attributes, true);
            
        }
        return $cols;
    }

    public function morphOrderRequest(array $orderRaw, array $colDef)
    {
        $order = [];
        $o = [];
        foreach($colDef as $k=>$v) {
            $o[$v['attr']] = $k;
        }
        // traverse order for some correction
        foreach($orderRaw as $k=>$v) {
            if(array_key_exists($k, $o)) {
                $order[] = [
                    'column' => (int)($o[$k] ?? 0),
                    'dir' => $v,
                ];     
            }      
        }
        return $order;
    }

    /**
     * Morphs the search request
     * 
     * - datatablesjs-friendly
     *
     * @param string|array $search
     * @return array
     */
    public function morphSearchRequest($search)
    {
        $r = is_array($search) && array_key_exists('regex', $search) && is_string($search['regex']) ? $search['regex'] : '';
        $v = is_array($search) && array_key_exists('value', $search) && is_string($search['value']) ? $search['value'] : '';
        $o = ['regex' => '', 'value' => ''];
        if(str_preg_match('/(\/(.+)\/u)/u', $r)) {
            $o['regex'] = $r;
        }
        if(!empty($v)) {
            $o['value'] = $v;
        }
        return $o;
    }







    // /**
    //  * Add the default settings, buttons, etc that this operation needs.
    //  */
    // protected function setupDeleteDefaults()
    // {
    //     $this->crud->allowAccess('delete');

    //     $this->crud->operation('delete', function () {
    //         $this->crud->loadDefaultOperationSettingsFromConfig();
    //     });

    //     $this->crud->operation(['list', 'show'], function () {
    //         $this->crud->addButton('line', 'delete', 'view', 'crud::buttons.delete', 'end');
    //     });
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  int  $id
    //  * @return string
    //  */
    // public function destroy($id)
    // {
    //     $this->crud->hasAccessOrFail('delete');

    //     // get entry ID from Request (makes sure its the last ID for nested resources)
    //     $id = $this->crud->getCurrentEntryId() ?? $id;

    //     return $this->crud->delete($id);
    // }



}
