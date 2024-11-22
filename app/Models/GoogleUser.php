<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;

class GoogleUser extends Model
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'avatar',
        'access_token',
        'refresh_token',
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
