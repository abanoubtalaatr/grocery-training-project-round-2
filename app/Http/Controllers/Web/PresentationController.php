<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Laravel\Passport\Client;

class PresentationController extends Controller
{
    public function index(): View
    {
        return view('presentation.index');
    }

    public function session(): View
    {
        return view('presentation.session');
    }

    public function sanctum(): View
    {
        return view('presentation.sanctum');
    }

    public function passport(): View
    {
        $clients = Client::query()->orderBy('name')->get();

        return view('presentation.passport', compact('clients'));
    }

    public function jwt(): View
    {
        return view('presentation.jwt');
    }

    public function compare(): View
    {
        return view('presentation.compare');
    }

    public function demos(): View
    {
        return view('presentation.demos');
    }
}
