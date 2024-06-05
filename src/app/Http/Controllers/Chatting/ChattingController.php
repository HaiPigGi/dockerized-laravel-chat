<?php

namespace App\Http\Controllers\Chatting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pusher\Pusher;
use App\Models\ChatMessageModel;
use App\Models\ChatRoomModel;
use Illuminate\Support\Facades\Log;
use App\Models\ChatRoomUserModel;
use App\Models\User;
class ChattingController extends Controller
{
     // send Message with pusher
     public function sendMessage(Request $request)
     {
         $data = $request->validate([
             'chat_room_id' => 'required|uuid',
             'user_id' => 'required|uuid',
             'message' => 'required|string'
         ]);
 
         // Pusher configuration
         $options = [
             'cluster' => env('PUSHER_APP_CLUSTER'),
             'useTLS' => true
         ];
         $pusher = new Pusher(
             env('PUSHER_APP_KEY'),
             env('PUSHER_APP_SECRET'),
             env('PUSHER_APP_ID'),
             $options
         );
 
         try {
             $channelName = 'chat.' . $data['chat_room_id'];
             //get data spesific name not id in chat room id
             $chatRoom = ChatRoomModel::where('chat_room_id', $data['chat_room_id'])->first();
             if (!$chatRoom) {
                return response()->json(['message' => 'Chat room not found'], 404);
            }
             $data['chat_room_name'] = $chatRoom->name;
             // Get the user's name
             $user = User::where('user_id', $data['user_id'])->first();
             if (!$user) {
                 return response()->json(['message' => 'User not found'], 404);
             }
             $data['user_name'] = $user->name;

            // Prepare the new message data
            $newMessage = [
                'room' => $data['chat_room_name'],
                'name' => $data['user_name'],
                'message' => $data['message']
            ];
             $pusherRes = $pusher->trigger($channelName, 'new-message', $newMessage);
             Log::info('Pusher response: ' . json_encode($pusherRes));
 
             // Save message to database if it was successfully sent
             if ($pusherRes) {
                 // Save the message
                 $chatMessage = new ChatMessageModel();
                 $chatMessage->chat_room_id = $data['chat_room_id'];
                 $chatMessage->user_id = $data['user_id'];
                 $chatMessage->message = $data['message'];
                 $chatMessage->save();
 
                 // Check if the user is already in the chat room
                 $chatRoomUser = ChatRoomUserModel::where('chat_room_id', $data['chat_room_id'])
                                                   ->where('user_id', $data['user_id'])
                                                   ->first();
 
                 // If the user is not in the chat room, add them
                 if (!$chatRoomUser) {
                     $chatRoomUser = new ChatRoomUserModel();
                     $chatRoomUser->chat_room_id = $data['chat_room_id'];
                     $chatRoomUser->user_id = $data['user_id'];
                     $chatRoomUser->save();
                 }
 
                 return response()->json($chatMessage, 201);
             } else {
                 return response()->json(['message' => 'Message not sent'], 500);
             }
         } catch (\Exception $e) {
             Log::error('Pusher error: ' . $e->getMessage());
             return response()->json(['message' => 'Message not sent due to an error'], 500);
         }
     }

    // get messages from database
    public function getMessages($chatRoomId)
    {
        $messages = ChatMessageModel::where('chat_room_id', $chatRoomId)->with('users')->get();
        return response()->json($messages);
    }

    // create room for chat
    public function createRoom (Request $request)
    {   
        Log::info($request->name);
        $request->validate([
            'name' => 'required'
        ]);

        $chatRoom = new ChatRoomModel();
        $chatRoom->name = $request->name;
        $chatRoom->save();
        
        return response()->json($chatRoom);
    }
}