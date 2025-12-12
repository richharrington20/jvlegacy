<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('legacy')->table('update_images', function (Blueprint $table) {
            if (!Schema::connection('legacy')->hasColumn('update_images', 'file_type')) {
                $table->string('file_type', 50)->nullable()->after('file_name');
            }
            if (!Schema::connection('legacy')->hasColumn('update_images', 'mime_type')) {
                $table->string('mime_type', 100)->nullable()->after('file_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('legacy')->table('update_images', function (Blueprint $table) {
            if (Schema::connection('legacy')->hasColumn('update_images', 'mime_type')) {
                $table->dropColumn('mime_type');
            }
            if (Schema::connection('legacy')->hasColumn('update_images', 'file_type')) {
                $table->dropColumn('file_type');
            }
        });
    }
};


