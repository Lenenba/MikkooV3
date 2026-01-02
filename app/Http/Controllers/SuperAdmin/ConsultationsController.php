<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Invoice;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConsultationsController extends Controller
{
    public function __invoke(Request $request)
    {
        return Inertia::render('superadmin/Consultations', [
            'metrics' => [
                'users' => [
                    'parents' => User::parents()->count(),
                    'babysitters' => User::babysitters()->count(),
                    'superadmins' => User::query()
                        ->whereHas('roles', fn($q) => $q->where('name', env('SUPER_ADMIN_ROLE_NAME', 'SuperAdmin')))
                        ->count(),
                ],
                'reservations' => [
                    'total' => Reservation::query()->count(),
                    'pending' => Reservation::query()
                        ->whereHas('details', fn($query) => $query->where('status', 'pending'))
                        ->count(),
                    'confirmed' => Reservation::query()
                        ->whereHas('details', fn($query) => $query->whereIn('status', ['confirmed', 'completed']))
                        ->count(),
                ],
                'invoices' => [
                    'issued' => Invoice::query()->where('status', 'issued')->count(),
                    'paid' => Invoice::query()->where('status', 'paid')->count(),
                ],
                'announcements' => [
                    'open' => Announcement::query()->where('status', 'open')->count(),
                    'total' => Announcement::query()->count(),
                ],
            ],
        ]);
    }
}
