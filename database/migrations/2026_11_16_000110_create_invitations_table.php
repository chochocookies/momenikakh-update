<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 30);
            $table->string('template_slug', 80)->nullable();
            $table->string('slug', 80)->unique();
            $table->string('title', 120);
            $table->string('status', 20)->default('draft');
            $table->string('locale', 5)->default('id');
            $table->string('timezone', 40)->default('Asia/Jakarta');
            $table->json('content')->nullable();   // free-text sections, keys defined per event type
            $table->json('settings')->nullable();  // palette, motion, feature switches
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
            $table->index('event_type');
        });

        Schema::create('invitation_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->string('name', 100);
            $table->string('nickname', 40)->nullable();
            $table->string('parents_text', 200)->nullable();
            $table->string('bio', 500)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('instagram', 30)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort']);
        });

        Schema::create('invitation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30);
            $table->string('label', 60)->nullable();
            $table->dateTime('starts_at');          // stored in UTC; displayed in invitations.timezone
            $table->dateTime('ends_at')->nullable();
            $table->string('venue_name', 120);
            $table->string('address', 300);
            $table->string('maps_url', 500)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('dress_code', 80)->nullable();
            $table->string('notes', 300)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'starts_at']);
        });

        Schema::create('invitation_story_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->string('body', 600);
            $table->date('happened_on')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort']);
        });

        Schema::create('invitation_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);             // bank | ewallet | address
            $table->string('provider', 60)->nullable();
            $table->string('holder_name', 100)->nullable();
            $table->string('number', 40)->nullable();
            $table->string('address_text', 300)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['invitation_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_gifts');
        Schema::dropIfExists('invitation_story_items');
        Schema::dropIfExists('invitation_events');
        Schema::dropIfExists('invitation_people');
        Schema::dropIfExists('invitations');
    }
};
