<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataLomba;


class JuriController extends Controller
{
    public function index()
    {
        return view('admin.crud.juri.index');
    }

    public function create()
    {
        return view('admin.crud.juri.create');
    }
}
