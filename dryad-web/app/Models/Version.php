<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Version extends Model
{
    protected $fillable = [
        'package_id',
        'number', // Ex: 1.0.0
        'description',
        'published_at',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(Dependency::class);
    }
}
