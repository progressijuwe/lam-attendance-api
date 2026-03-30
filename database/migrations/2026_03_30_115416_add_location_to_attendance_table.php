<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->after('picture_path');
            $table->decimal('lng', 10, 7)->after('lat');
            $table->decimal('distance_meters', 8, 2)->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'distance_meters']);
        });
    }
};