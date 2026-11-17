<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('group', 60)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('token', 16)->unique();   // used for the optional &g= "opened" ping
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->index(['invitation_id', 'name']);
        });

        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('attendance', 10);        // yes | no | maybe
            $table->unsignedTinyInteger('pax')->default(1);
            $table->string('note', 300)->nullable();
            $table->char('ip_hash', 64)->nullable();  // salted hash for abuse control, never the raw IP
            $table->timestamps();

            $table->index(['invitation_id', 'attendance']);
            $table->index(['invitation_id', 'guest_id']);
        });

        Schema::create('guest_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->text('message');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['invitation_id', 'is_visible', 'created_at']);
        });

        Schema::create('invitation_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->unsignedInteger('opens')->default(0);   // cover opened
            $table->timestamps();

            $table->unique(['invitation_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_daily_stats');
        Schema::dropIfExists('guest_messages');
        Schema::dropIfExists('rsvps');
        Schema::dropIfExists('guests');
    }
};
