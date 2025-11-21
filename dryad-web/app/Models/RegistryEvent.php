<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistryEvent extends Model
{
    protected $fillable = [
        'package_id',
        'user_id',
        'action', // Ex: "publish", "update", "delete"
        'details', // JSON ou texto
        'created_at',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
