<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidekick_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider');
            $table->string('model');
            $table->text('system_prompt')->nullable();
            $table->unsignedInteger('max_tokens')->default(1024);
            $table->timestamps();
        });

        Schema::create('sidekick_conversation_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')
                ->references('id')
                ->on('sidekick_conversations')
                ->onDelete('cascade');
            $table->string('role');
            $table->longText('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidekick_conversation_messages');
        Schema::dropIfExists('sidekick_conversations');
    }
};
