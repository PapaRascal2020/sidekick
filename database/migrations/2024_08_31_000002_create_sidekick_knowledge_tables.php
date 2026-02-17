<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidekick_knowledge_bases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('embedding_provider')->nullable();
            $table->string('embedding_model')->nullable();
            $table->unsignedInteger('chunk_size')->default(2000);
            $table->unsignedInteger('chunk_overlap')->default(200);
            $table->timestamps();
        });

        Schema::create('sidekick_knowledge_chunks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('knowledge_base_id')
                ->references('id')
                ->on('sidekick_knowledge_bases')
                ->onDelete('cascade');
            $table->longText('content');
            $table->json('embedding')->nullable();
            $table->string('source')->nullable();
            $table->unsignedInteger('chunk_index')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidekick_knowledge_chunks');
        Schema::dropIfExists('sidekick_knowledge_bases');
    }
};
