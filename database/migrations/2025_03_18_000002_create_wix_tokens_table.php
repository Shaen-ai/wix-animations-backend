<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wix_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('instance')->index();
            $table->string('app', 50)->default('sitenoticebanner');
            $table->text('access_token')->nullable();
            $table->timestamp('acc_expires_at')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('ref_expires_at')->nullable();
            $table->json('info')->nullable();
            $table->timestamps();

            $table->unique(['instance', 'app']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wix_tokens');
    }
};
