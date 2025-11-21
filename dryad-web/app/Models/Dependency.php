<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dependency extends Model
{
    protected $fillable = [
        'version_id',
        'package_name',
        'required_version', // Ex: ">=1.0.0"
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }
}
