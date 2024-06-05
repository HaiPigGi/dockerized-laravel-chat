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
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->uuid('chat_room_id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->uuid('chat_message_id')->primary();
            $table->uuid('chat_room_id');
            $table->uuid('user_id');
            $table->text('message');
            $table->timestamps();

            $table->foreign('chat_room_id')->references('chat_room_id')->on('chat_rooms')->onDelete('cascade');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('chat_room_users', function (Blueprint $table) {
            $table->uuid('chat_room_user_id')->primary();
            $table->uuid('chat_room_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->foreign('chat_room_id')->references('chat_room_id')->on('chat_rooms')->onDelete('cascade');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_room_users');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_rooms');
    }
};