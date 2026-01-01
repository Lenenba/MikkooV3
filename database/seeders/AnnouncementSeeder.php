<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AnnouncementSeeder extends Seeder
{
    /**
     * Seed announcements with child details for demo data.
     */
    public function run(): void
    {
        $parents = User::parents()->get();
        if ($parents->isEmpty()) {
            $this->command->warn('No parents found. Skipping announcements seeding.');
            return;
        }

        $services = Service::query()
            ->whereNull('user_id')
            ->orderBy('name')
            ->pluck('name')
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->values();

        if ($services->isEmpty()) {
            $this->command->warn('No catalog services found. Skipping announcements seeding.');
            return;
        }

        $childNames = ['Lina', 'Noah', 'Maya', 'Adam', 'Sara', 'Leo', 'Mila', 'Ilyes'];
        $childAges = ['2 ans', '3 ans', '4 ans', '5 ans', '6 ans', '7 ans'];
        $childNotes = [
            'Aime les jeux calmes et les histoires.',
            'Allergie aux arachides.',
            'Besoin de routine pour le coucher.',
            'Aime les activites en plein air.',
            'Aide aux devoirs apres ecole.',
        ];
        $titleTemplates = [
            'Besoin de %s',
            'Recherche %s',
            'Demande: %s',
            '%s pour cette semaine',
            '%s pour ce week-end',
        ];
        $descriptions = [
            'Horaires flexibles, merci de preciser vos disponibilites.',
            'Nous cherchons une personne calme et attentive.',
            'Sortie d\'ecole et retour a la maison.',
            'Garde ponctuelle, maison non fumeur.',
            'Possibilite de garde recurrente.',
        ];

        $count = min(12, max(6, $parents->count() * 2));
        $created = 0;

        $randomBool = static fn(int $percent) => mt_rand(1, 100) <= $percent;

        for ($i = 0; $i < $count; $i++) {
            $parent = $parents->random();
            $service = $services->random();
            $title = sprintf(Arr::random($titleTemplates), $service);
            $childName = Arr::random($childNames);
            $childAge = Arr::random($childAges);
            $childNote = $randomBool(70) ? Arr::random($childNotes) : null;
            $description = $randomBool(85) ? Arr::random($descriptions) : null;
            $startDate = Carbon::now()->addDays(rand(1, 21))->toDateString();
            $startHour = rand(7, 18);
            $durationHours = rand(2, 4);
            $startTime = sprintf('%02d:00', $startHour);
            $endTime = sprintf('%02d:00', min(22, $startHour + $durationHours));
            $isRecurring = $randomBool(30);
            $scheduleType = $isRecurring ? 'recurring' : 'single';
            $recurrenceFrequency = $isRecurring ? 'weekly' : null;
            $recurrenceInterval = $isRecurring ? 1 : null;
            $recurrenceDays = $isRecurring ? [Carbon::parse($startDate)->isoWeekday()] : null;
            $recurrenceEndDate = $isRecurring
                ? Carbon::parse($startDate)->addWeeks(rand(3, 8))->toDateString()
                : null;

            Announcement::create([
                'parent_id' => $parent->id,
                'title' => $title,
                'service' => $service,
                'children' => [
                    [
                        'id' => 0,
                        'name' => $childName,
                        'age' => $childAge,
                        'allergies' => null,
                        'details' => null,
                        'photo' => null,
                    ],
                ],
                'child_name' => $childName,
                'child_age' => $childAge,
                'child_notes' => $childNote,
                'description' => $description,
                'location' => $parent->address?->city,
                'start_date' => $startDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'schedule_type' => $scheduleType,
                'recurrence_frequency' => $recurrenceFrequency,
                'recurrence_interval' => $recurrenceInterval,
                'recurrence_days' => $recurrenceDays,
                'recurrence_end_date' => $recurrenceEndDate,
                'status' => $randomBool(75) ? 'open' : 'closed',
            ]);

            $created++;
        }

        $this->command->info("Announcements seeded: {$created}.");
    }
}
