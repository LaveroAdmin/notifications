<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        if (!Schema::connection($connection)->hasTable('notification_channels')) {
            Schema::connection($connection)->create('notification_channels', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('driver', 32)->index(); // telegram, webhook, email
                $table->text('config')->nullable(); // JSON: bot_token, chat_id, url, email
                $table->text('events')->nullable(); // JSON array of event keys
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (!Schema::connection($connection)->hasTable('notification_logs')) {
            Schema::connection($connection)->create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('channel_id')->nullable()->index();
                $table->string('event', 100)->index();
                $table->string('title', 255);
                $table->text('message')->nullable();
                $table->string('status', 20)->default('sent')->index();
                $table->text('error')->nullable();
                $table->timestamps();

                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        Schema::connection($connection)->dropIfExists('notification_logs');
        Schema::connection($connection)->dropIfExists('notification_channels');
    }
};
