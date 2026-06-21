<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Global Memory — Clients
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('preferred_style')->nullable();   // Professional, Friendly, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Global Memory — Contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Global Memory — Assets (domains, SSL, SaaS, hosting)
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. example.com
            $table->enum('type', ['SSL', 'Domain', 'Hosting', 'SaaS', 'Other']);
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('vendor')->nullable();
            $table->date('renewal_date')->nullable();
            $table->string('service_owner')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Ava-specific Rules (AVA-001 through AVA-006 and beyond)
        Schema::create('ava_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_id')->unique();             // e.g. AVA-001
            $table->text('condition');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical']);
            $table->text('action');
            $table->boolean('approval_required')->default(true);
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Renewal register — final logged output per transaction
        Schema::create('renewal_register', function (Blueprint $table) {
            $table->id();
            $table->string('tx_id');
            $table->string('category');
            $table->string('asset');
            $table->string('client');
            $table->string('contact');
            $table->date('due_date')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical']);
            $table->string('status');
            $table->string('draft_id')->nullable();
            $table->string('human_decision')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_register');
        Schema::dropIfExists('ava_rules');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('clients');
    }
};
