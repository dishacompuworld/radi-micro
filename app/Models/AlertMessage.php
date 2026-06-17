<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertMessage extends Model
{
    // use HasFactory;
    protected $fillable = ['type', 'message', 'category', 'msgcode'];
}
