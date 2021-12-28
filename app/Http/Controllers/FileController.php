<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function uploadImage(Request $request){
    $answer = ['status' => 1, 'msg' => ''];
        try {
            if ($request->file('photo')){
                $image = $request->file('photo')->store('images');
                $answer['msg'] = storage_path("app/$image");
                $answer['msg'] = public_path("app/$image");
            } else {
                $answer['msg'] = "There's no file";
            }
        } catch (\Exception $e) {
            $answer['status'] = 0;
            $answer['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($answer);
    }
}
