<?php

namespace PrivateEvent\Apidox\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;
use PrivateEvent\Apidox\Services\RouteScanner;

class ApidoxController extends Controller
{
    public function __construct(private RouteScanner $scanner) {}

    public function index(): View
    {
        $endpoints = $this->scanner->scan();

        return view('apidox::index', [
            'endpoints' => $endpoints,
            'config' => config('apidox'),
        ]);
    }
}
