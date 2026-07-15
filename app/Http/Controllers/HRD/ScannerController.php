<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    /**
     * Display the scanner tool page.
     */
    public function index()
    {
        return view('hrd.scanner.index');
    }
}
