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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('calendar_id')->constrained();
            $table->string('href');
            $table->string('etag');
            $table->string('ical');

            $table->boolean('completed');
            $table->string('summary');
            $table->string('uid');
            $table->string('description');
            $table->string('due');
            $table->integer('priority');
            $table->string('tags');
            $table->string('parent_uid');

            $table->timestamps();

            $table->unique(['calendar_id', 'href']);
            $table->unique(['calendar_id', 'uid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
