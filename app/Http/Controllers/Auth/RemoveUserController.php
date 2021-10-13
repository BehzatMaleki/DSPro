<?php

namespace App\Http\Controllers\Auth;
use DB;

use Illuminate\Http\Request;
use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class RemoveUserController extends Controller
{
    public function remove_user(Request $request){
        $u_id = $request->input('u_id');
        $sql= "delete from users where id=$u_id";
        $result= DB::delete($sql);
        return redirect()->to('/manage'); 
    }
}