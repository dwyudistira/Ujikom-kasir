<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    //
    public function index(){
        $purchases = Sales::paginate(10);

        return view('admin.pembelian.index', compact('purchases'));
    }
}

