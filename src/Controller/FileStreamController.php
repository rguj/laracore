<?php

namespace Rguj\Laracore\Controller;

use App\Http\Controllers\Controller;
// use Rguj\Laracore\Controller\Controller;
use Rguj\Laracore\Request\Request;

use Illuminate\Support\Str;

use Exception;

use App\Http\Controllers\Student\Link as StudentLink;

class FileStreamController extends Controller {

    public function index(Request $request) {
        return storage_file_stream($request);
    }
    
    public function store(Request $request) {
        return storage_file_stream($request);
    }

}
    


