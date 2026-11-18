<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('artist', 120)->nullable();
            $table->string('license', 120)->nullable();          // e.g. the exact licence name or contract reference
            $table->string('license_url', 500)->nullable();      // proof: link to the licence or the purchase record
            $table->boolean('requires_attribution')->default(false);
            $table->string('attribution_text', 300)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('verified_at')->nullable();        // set by an admin after checking the licence proof
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_tracks');
    }
};
