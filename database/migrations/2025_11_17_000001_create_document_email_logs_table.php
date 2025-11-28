<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('legacy')->create('document_email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('document_id')->nullable();
            $table->string('document_name')->nullable();
            $table->string('recipient');
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamp('sent_at')->useCurrent();

            $table->index('project_id');
            $table->index('account_id');
            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::connection('legacy')->dropIfExists('document_email_logs');
    }
};


