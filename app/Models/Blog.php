<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model; // Use MongoDB model

class Blog extends Model
{
    use HasFactory;

    // Specify the collection name if it differs from the model name
    protected $collection = 'blog';

    // Define fillable fields for mass assignment
    protected $fillable = ['key', 'content', 'hash'];
}
