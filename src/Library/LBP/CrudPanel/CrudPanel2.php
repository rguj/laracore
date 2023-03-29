<?php

namespace Rguj\Laracore\Library\LBP\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;

/**
 * CrudPanel extended
 * 
 * - declare new or modified functions here
 */
class CrudPanel2
{

    // public $crud;

    // public function __construct(CrudPanel $crud)
    // {
    //     $this->crud = $crud;
    // }

    public array $exportButtons = [
        'copy',
        'csv',
        'excel',
        'pdf',
        'print',
    ];




    /**
     * Get the request instance for this CRUD.
     *
     * @return \Illuminate\Http\Request
     */
    public function __getRequest()
    {
        return app('crud')->getRequest();
    }    

    public function __hasRequest(string $key)
    {
        return $this->__getRequest()->has($key);
    }

    public function __getOperationSetting(string $key, $operation = null)
    {
        return app('crud')->getOperationSetting($key, $operation);
    }

    public function __setOperationSetting(string $key, $value, $operation = null)
    {
        return app('crud')->setOperationSetting($key, $value, $operation);
    }

    public function __getExportButtonShow()
    {
        return (array)$this->__getOperationSetting('exportButtonShow');
    }
    
    public function __setExportButtonShow(array $buttons)
    {
        app('crud')->setOperationSetting('exportButtonShow', $buttons);
    }













    


    /**
     * Enable a specific export button
     *
     * @param string $name
     * @return void
     */
    public function enableExportButton(string $name, bool $is_init = false)
    {
        $exportButtonShow = $this->__getExportButtonShow();

        if($is_init) {
            if(!in_array($name, $exportButtonShow, true)) {
                $exportButtonShow[] = $name;
            }
        } else {
            if(!in_array($name, $exportButtonShow, true)) {
                $exportButtonShow[] = $name;
            }
        }

        $this->__setExportButtonShow($exportButtonShow);
    }

    /**
     * Disable a specific export button
     *
     * @param string $name
     * @return void
     */
    public function disableExportButton(string $name)
    {
        $exportButtonShow = $this->__getExportButtonShow();

        if($key = array_search($name, $exportButtonShow, true)) {
            unset($exportButtonShow[$key]);
        }

        $this->__setExportButtonShow($exportButtonShow);
    }











    /**
     * Enable all export buttons
     *
     * @param string $name
     * @return void
     */
    public function enableExportButtons(bool $is_init = false)
    {
        $this->__setOperationSetting('exportButtons', true);
        $this->__setOperationSetting('showExportButton', true);
        
        foreach($this->exportButtons as $k=>$v) {
            $this->enableExportButton($v, $is_init);
        }
    }

    /**
     * Disable all export buttons
     *
     * @param string $name
     * @return void
     */
    public function disableExportButtons()
    {
        $this->__setOperationSetting('exportButtons', false);
        $this->__setOperationSetting('showExportButton', false);

        foreach($this->exportButtons as $k=>$v) {
            $this->disableExportButton($v);
        }
    }




















    /**
     * Enable all export button
     *
     * @return void
     */
    public function enableColumnVisibilityButton()
    {
        $this->__setOperationSetting('showTableColumnPicker', true);
    }
    
    /**
     * Disable all export button
     *
     * @return void
     */
    public function disableColumnVisibilityButton()
    {
        $this->__setOperationSetting('showTableColumnPicker', false);
    }











    /**
     * Set the fallback data of the column order
     *
     * @param string $column
     * @param string $dir
     * @return void
     */
    public function setColumnOrderFallback(string $column, string $dir = 'asc')
    {
        if(!$this->__hasRequest('order')) {
            $this->__setOperationSetting('order', [['column' => $column, 'dir' => $dir]]);
        }
    }

    /**
     * Replaces the column order data
     * 
     * - enclose it in a double bracket, e.g. `[[ ]]`
     *
     * @param <int,<string,string>> $data
     * @return void
     */
    public function setColumnOrderData(array $data)
    {
        $this->__setOperationSetting('order', $data);
    }






}
