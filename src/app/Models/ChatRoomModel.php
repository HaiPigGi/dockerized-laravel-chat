<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class ChatRoomModel extends Model
{
    use HasFactory;

    protected $table = 'chat_rooms';

    protected $primaryKey = 'chat_room_id';

    public $incrementing=false;

    protected $keyType = "string";

    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_room_users', 'chat_room_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessageModel::class, 'chat_room_id', 'chat_room_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->chat_room_id = (string) Str::uuid();
        });
    }

    
}