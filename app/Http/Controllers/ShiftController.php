<?php

namespace App\Http\Controllers;
use DB;

use Illuminate\Http\Request;
use Auth;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class ShiftController extends Controller
{
    public function shift_request()
    {
        return view('shift_request');
    }

    //New shift request
    public function insert_shift_req(Request $request){
        $name = Auth::user()->name;
        $role = Auth::user()->role;
        $job = Auth::user()->job;
        $shift = $request->input('shift');
        $s_date = $request->input('s_date');
        $data=array('name'=>$name,"role"=>$role,"job"=>$job,"shift"=>$shift,"date"=>$s_date);
        DB::table('requests')->insert($data);
        return redirect()->to('/home'); 
    }

    //Delet shift request
    public function del_shift_req(Request $request){
        $req_id = $request->input('s_req_id');
        $sql= "delete from requests where req_id=$req_id";
        $result= DB::delete($sql);
        return redirect()->to('/home'); 
    }

    //Confirm shift request
    public function confirm_shift(Request $request){
        $req_id = $request->input('s_req_id');
        $sql= "select * from requests where req_id=$req_id";
        $current_request = DB::select($sql);
        foreach ($current_request as $row) {
            $name = $row->name;
            $role = $row->role;
            $job = $row->job;
            $shift = $row->shift;
            $s_date = $row->date;
        }
        $data=array('name'=>$name,"role"=>$role,"job"=>$job,"shift"=>$shift,"date"=>$s_date);
        DB::table('shifts')->insert($data);
        $sql= "delete from requests where req_id=$req_id";
        $result= DB::delete($sql);
        return redirect()->to('/home'); 
    }

    //Delet shift
    public function del_shift(Request $request){
        $shift_id = $request->input('shift_id');
        $sql= "delete from shifts where shift_id=$shift_id";
        $result= DB::delete($sql);
        return redirect()->to('/home'); 
    }
}