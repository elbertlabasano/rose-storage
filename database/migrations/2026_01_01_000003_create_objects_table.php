<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('objects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bucket_id')->constrained('buckets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key', 255)->unique();
            $table->string('original_name', 255);
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('size');
            $table->char('checksum', 64);
            $table->string('disk', 32);
            $table->string('visibility', 16)->default('private');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['bucket_id', 'created_at']);
            $table->index(['user_id', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objects');
    }
};


