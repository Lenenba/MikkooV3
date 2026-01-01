<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isParent()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'service' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $description = isset($data['description']) ? trim((string) $data['description']) : null;
        $description = $description !== '' ? $description : null;

        Announcement::create([
            'parent_id' => $user->id,
            'title' => trim($data['title']),
            'service' => trim($data['service']),
            'description' => $description,
            'status' => 'open',
        ]);

        return back()->with('success', 'Annonce publiee.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        if (!$user || !$user->isParent()) {
            abort(403);
        }

        if ((int) $announcement->parent_id !== (int) $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:open,closed'],
        ]);

        $announcement->update([
            'status' => $data['status'],
        ]);

        return back()->with('success', 'Annonce mise a jour.');
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $user = $request->user();
        if (!$user || !$user->isParent()) {
            abort(403);
        }

        if ((int) $announcement->parent_id !== (int) $user->id) {
            abort(403);
        }

        $announcement->delete();

        return back()->with('success', 'Annonce supprimee.');
    }
}
