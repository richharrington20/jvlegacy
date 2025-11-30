<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('legacy')->create('update_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('update_id'); // project_log id
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->datetime('created_on')->nullable();
            $table->boolean('deleted')->default(false);
            
            $table->index('update_id');
            $table->index('display_order');
        });
    }

    public function down(): void
    {
        Schema::connection('legacy')->dropIfExists('update_images');
    }
};

