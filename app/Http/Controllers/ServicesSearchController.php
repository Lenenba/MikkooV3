<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

/**
 * Class ServicesSearchController
 * @package App\Http\Controllers
 *
 * This controller handles the search functionality for services.
 */
class ServicesSearchController extends Controller
{

    public function __invoke(Request $request)
    {
        $query = $request->input('query');
        $babysitterId = $request->input('babysitter_id');

        $servicesQuery = Service::where('name', 'like', "%{$query}%");
        if ($babysitterId) {
            $servicesQuery->where('user_id', $babysitterId);
        }

        $services = $servicesQuery
            ->limit(5)
            ->get();

        return response()->json($services);
    }
}
