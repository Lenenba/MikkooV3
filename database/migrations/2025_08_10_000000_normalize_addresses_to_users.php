<?php

use App\Models\User;
use App\Models\ParentProfile;
use App\Models\BabysitterProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->normalizeProfileAddresses(BabysitterProfile::class);
        $this->normalizeProfileAddresses(ParentProfile::class);

        Schema::table('addresses', function (Blueprint $table) {
            $table->index('city');
            $table->index('country');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropIndex(['country']);
            $table->dropIndex(['latitude', 'longitude']);
        });
    }

    private function normalizeProfileAddresses(string $profileClass): void
    {
        $profileClass::with(['address', 'user'])->chunkById(100, function ($profiles) {
            foreach ($profiles as $profile) {
                $address = $profile->address;
                if (! $address) {
                    continue;
                }

                $user = $profile->user;
                if (! $user) {
                    continue;
                }

                if ($user->address()->exists()) {
                    $address->delete();
                    continue;
                }

                $address->addressable_id = $user->id;
                $address->addressable_type = User::class;
                $address->save();
            }
        });
    }
};
