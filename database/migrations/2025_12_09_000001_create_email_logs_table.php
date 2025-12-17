<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('legacy')->create('email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message_id')->nullable()->unique(); // Postmark message ID
            $table->string('email_type')->index(); // project_update, project_documents, welcome, etc.
            $table->string('recipient_email')->index();
            $table->string('recipient_name')->nullable();
            $table->unsignedBigInteger('recipient_account_id')->nullable()->index();
            $table->string('subject');
            $table->text('html_content')->nullable();
            $table->text('text_content')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'sent', 'delivered', 'bounced', 'spam_complaint', 'failed'])->default('pending')->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            
            // Postmark tracking
            $table->string('postmark_message_id')->nullable()->index();
            $table->text('postmark_response')->nullable(); // JSON response from Postmark
            $table->text('error_message')->nullable();
            
            // Related entities
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('update_id')->nullable()->index();
            $table->unsignedBigInteger('sent_by')->nullable()->index(); // User who triggered the send
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional context (template used, variables, etc.)
            $table->integer('open_count')->default(0);
            $table->integer('click_count')->default(0);
            
            $table->timestamps();
            
            $table->index(['status', 'sent_at']);
            $table->index(['email_type', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::connection('legacy')->dropIfExists('email_logs');
    }
};

