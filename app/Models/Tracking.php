<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $table = 'tracking';
    protected $primaryKey = 'tracking_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'tracking_id',
        'warehouse',
        'comments',
        'shipped',
        'created',
        'modified',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'created' => 'datetime',
        'modified' => 'datetime',
    ];
}
