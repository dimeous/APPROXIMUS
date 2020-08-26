<?php

namespace App\Http\Controllers;

//

use DB;
use Illuminate\Support\Facades\Request;

class settingsController extends testController
{


    function index(){
     $fiat =testController::getFiat();
     $crypto =testController::getCrypto();
        $currs = DB::table('bestchange_currs')
            ->get()->keyBy('id')->toArray();
     return view('settings',
         [
             'crypto'=>$crypto,
             'fiat'=>$fiat,
             'currs'=>$currs,
             'sfiat'=>   \Cache::get('fiat'),
             'scrypto'=>   \Cache::get('crypto')
         ]);
    }
     function save(Request $req){
        $arr = $req::all();
        \Cache::put('checkbox_min_rub',intval($req::get('checkbox_min_rub')));
        \Cache::put('checkbox_min_rub_sum',intval($req::get('checkbox_min_rub_sum')));
         \Cache::put('checkbox_minus',intval($req::get('checkbox_minus')));
         $fiat =testController::getFiat();
         $crypto =testController::getCrypto();

         $f=[];
         foreach ($fiat as $k=>$v)
             if (isset($arr["fiat-$k"]))
                $f[$k]=$arr["fiat-$k"];
         \Cache::put('fiat',$f);
         $c=[];
         foreach ($crypto as $k=>$v)
             if (isset($arr["crypto-$k"]))
                $f[$k]=$arr["crypto-$k"];
         \Cache::put('crypto',$f);
         return redirect('/');
     }

}
