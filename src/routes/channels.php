<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\ChatRoomUserModel;

// create private channel

// create private channel
Broadcast::channel('chat.{chatRoomId}', function ($user, $chatRoomId) {
    // Check if the user is part of the chat room
    return ChatRoomUserModel::where('chat_room_id', $chatRoomId)
                             ->where('user_id', $user->id)
                             ->exists();
});