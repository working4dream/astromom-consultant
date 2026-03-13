<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AwsS3Trait;
use Illuminate\Database\Eloquent\SoftDeletes;


class Message extends Model
{
    use HasFactory, AwsS3Trait, SoftDeletes;

    protected $fillable = [
        'session_id',
        'sender_id',
        'receiver_id',
        'message',
        'message_types',
        'video_path',
        'image_path',
        'audio_path',
        'status',
        'is_read',
        'reply_to_id',
        'conversation_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function getImagePathAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }
    public function getVideoPathAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }
    public function getAudioPathAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }
    public function replyTo()
    {
        return $this->belongsTo(Message::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class);
    }
    public function receiver()
    {
        return $this->belongsTo(User::class);
    }
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
