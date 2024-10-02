<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the Stripe Account columns for the user.
 */
return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sidekick_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('class');
            $table->string('model');
            $table->string('system_prompt')->nullable();
            $table->bigInteger('max_tokens');
            $table->timestamps();
        });

        Schema::create('sidekick_conversation_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')
                ->references('id')
                ->on('sidekick_conversations')
                ->constrained()
                ->onDelete('cascade');
            $table->string('role');
            $table->longtext('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sidekick_conversations');
        Schema::dropIfExists('sidekick_conversation_messages');
    }
};
