<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telegram extends Model
{
    protected $connection = 'singlestore';
    
    protected $fillable = [
        'device_id',
        'site_id',
        'telegram_timestamp',
        'batch_timestamp',
        'source',
        'destination',
        'service',
        'data',
        'message_code',
        'data_value',
        'direction',
        'sqs_message_id',
        'timestamp',
    ];
    
    protected $casts = [
        'telegram_timestamp' => 'datetime',
        'batch_timestamp' => 'datetime',
    ];
    
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'device_id', 'device_id');
    }
    
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
