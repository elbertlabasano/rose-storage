<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('idcode', 64)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->string('keycloak_issuer', 191)->nullable();
            $table->string('keycloak_subject', 191)->nullable();
            $table->unique(['keycloak_issuer', 'keycloak_subject'], 'users_keycloak_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_keycloak_identity_unique');
            $table->dropUnique(['idcode']);
            $table->dropColumn(['idcode', 'is_active', 'keycloak_issuer', 'keycloak_subject']);
        });
    }
};
