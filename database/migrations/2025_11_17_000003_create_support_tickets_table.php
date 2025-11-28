<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('legacy')->create('support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('account_id');
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('open');
            $table->timestamps();

            $table->index('project_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::connection('legacy')->dropIfExists('support_tickets');
    }
};


