<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Notifications\ReservationMediaRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationMediaController extends Controller
{
    public function store(Request $request, Reservation $reservation): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->back();
        }

        if (! $this->canAccess($user, $reservation)) {
            abort(403);
        }

        $reservation->loadMissing('details');
        $status = $reservation->details?->status;
        if (! in_array($status, ['in_progress', 'completed'], true)) {
            return redirect()->back()->withErrors([
                'media' => __('reservations.media.errors.not_allowed'),
            ]);
        }

        $request->validate([
            'media' => ['required', 'array', 'min:1'],
            'media.*' => ['required', 'file', 'mimes:jpg,png,jpeg,webp,mp4', 'max:25600'],
        ]);

        $files = $request->file('media', []);
        $incomingCount = is_array($files) ? count($files) : 0;
        $existingCount = $reservation->media()
            ->where('collection_name', 'reservation')
            ->count();

        if ($existingCount + $incomingCount > 10) {
            return redirect()->back()->withErrors([
                'media' => __('reservations.media.errors.limit_reached'),
            ]);
        }

        foreach ($files as $file) {
            $path = $file->store('reservation-media', 'public');
            $reservation->media()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'is_profile_picture' => false,
                'collection_name' => 'reservation',
                'custom_properties' => [
                    'uploaded_by' => $user->id,
                    'uploaded_role' => $user->role,
                ],
            ]);
        }

        if ($user->isBabysitter()) {
            $pendingRequest = $reservation->mediaRequests()
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->first();

            if ($pendingRequest) {
                $pendingRequest->update([
                    'status' => 'fulfilled',
                    'fulfilled_by' => $user->id,
                    'fulfilled_at' => now(),
                ]);
                $pendingRequest->requester?->notify(
                    new ReservationMediaRequestNotification($pendingRequest, 'fulfilled')
                );
            }
        }

        return redirect()->back()->with('success', __('flash.reservation.media_uploaded'));
    }

    protected function canAccess($user, Reservation $reservation): bool
    {
        if ($user->isParent()) {
            return (int) $reservation->parent_id === (int) $user->id;
        }

        if ($user->isBabysitter()) {
            return (int) $reservation->babysitter_id === (int) $user->id;
        }

        return false;
    }
}
