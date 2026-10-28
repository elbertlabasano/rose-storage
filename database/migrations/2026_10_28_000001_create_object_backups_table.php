<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Deliberately independent of objects: deleting an upload retains its backup.
        Schema::create('object_backups', function (Blueprint $table) {
            $table->uuid('object_id')->primary();
            $table->string('destination', 64);
            $table->char('checksum', 64);
            $table->json('manifest');
            $table->timestamp('verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('object_backups');
    }
};
