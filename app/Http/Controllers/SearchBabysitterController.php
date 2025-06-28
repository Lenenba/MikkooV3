<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SearchBabysitterController extends Controller
{
    use AuthorizesRequests;
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return \Inertia\Response
     */
    public function __invoke(?Request $request)
    {
        $filters = $request->only([
            'name',
        ]);
        $babysitters = User::Babysitters()
            ->with(['address', 'babysitterProfile', 'media'])
            ->MostRecent()
            ->Filter($filters)
            ->simplePaginate(10)
            ->withQueryString(); 
        return Inertia::render('search/Index', [
            'babysitters' => $babysitters,
            'filters' => $filters,
        ]);
    }
}
