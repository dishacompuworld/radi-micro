<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\HasActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
class Location extends Model
{
     use HasFactory, HasActivity, LogsActivity;
    public $timestamps = false;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'url', 'enable'])
        ->useLogName('Location')
        ->logOnlyDirty();
    }


    public function getDescriptionForEvent(string $eventName): string
    {
        return "Location {$eventName}";
    }
}
