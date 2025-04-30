<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubKategori;


class JuriController extends Controller
{
    public function index()
    {
        return view('crud.juri.index');
    }

    public function create()
    {
        return view('crud.juri.create');
    }
}
