<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $guarded = ['id'];

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }
}
