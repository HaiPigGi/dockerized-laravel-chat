<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class ChatMessageModel extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';
    
    protected $primaryKey = 'chat_message_id';
    
    public $incrementing=false;
    protected $keyType = "string";

    protected $fillable = ['chat_room_id', 'user_id', 'message'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoomModel::class, 'chat_room_id', 'chat_room_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->chat_message_id = (string) Str::uuid();
        });
    }

    
}