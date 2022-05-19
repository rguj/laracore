<?php

namespace Rguj\Laracore\Controller;

use App\Http\Controllers\Controller;
use Rguj\Laracore\Request\Request;
use App\Models\API;
use Illuminate\Support\Facades\DB;

class APIController extends Controller
{    
    public function index(Request $request)
    {
        $pathkey = $request->query('p');
        if(!is_null($pathkey)) {
            $pathkey = (string)$pathkey;
            $db = API::where(['pathkey'=>$pathkey]);

            if($db->count() < 1)
                abort(404);

            $data = $db->first()->getRawOriginal();
            dd($data);

            $cls = '\App\Http\Controllers\\'.$data['controller'];

            dd($db->get());

        }
        

    }





}
