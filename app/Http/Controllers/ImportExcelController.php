<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportExcelController extends Controller
{
    //
    public function import_excel(){
        return view('excel');
    }

    public function import_excel_post(Request $request){
        // dd($request->all());
        // dd($request->all());
        // dd($request->file('excel_file'));
        Excel::import(new UsersImport,$request->file('excel_file'));

        return redirect()->back()->with('success', 'Data tim berhasil diimport!');
    }
}
