<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Favorite\IndexFavoriteAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request, IndexFavoriteAction $action): View
    {
        $favorites = $action->run($request);

        return view('admin.favorites.index', compact('favorites'));
    }
}
