<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DOM-13: one event may be in another zone than the invitation (akad in WITA, reception in WIB).
        // null = the zone of the invitation, so every existing row keeps its meaning.
        Schema::table('invitation_events', function (Blueprint $table) {
            $table->string('timezone', 40)->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('invitation_events', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
