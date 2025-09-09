<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    /**
     * The database connection to use.
     */
    protected $connection = 'singlestore';

    /**
     * The table associated with the model.
     */
    protected $table = 'analytics';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'event_type',
        'user_id',
        'company_id',
        'site_id',
        'data',
        'timestamp'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'data' => 'array',
        'timestamp' => 'datetime'
    ];
}