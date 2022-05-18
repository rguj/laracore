<?php
namespace Rguj\Laracore\Macro;

class EloquentCollectionMacro
{

    /**
     * Converts eloquent collection to array
     *
     * @param null|object $obj (optional)
     * @return array
     */
    public function toArr()
    {
        return function() {
            $num_args = func_num_args();
            if($num_args > 1) throw new \Exception('Must have 0 or 1 argument');
            $arr = json_decode(json_encode($this), true);
            return $num_args === 0 ? $arr : ($arr[func_get_arg(0)]);
        };
    }

    public function toArr2()
    {
        return function() {
            return $this;
        };
    }
    
    // public function arrGet()
    // {
    //     return function() {
    //         dd(123213);

    //         if(!is_array($this)) throw new \Exception('$this must be array');
    //         return arr_get($this, $key, $default);
    //     };
    // }


}