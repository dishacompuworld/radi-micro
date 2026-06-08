<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
class Server extends Model
{
    use HasFactory, LogsActivity;

    public $timestamps = false;

    // protected static $logAtttibutes = ['name', 'mip','shortname', 'username','enable'];

    // protected static $logName = 'Server';


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'mip','shortname', 'username','enable'])
        ->useLogName('Server')
        ->logOnlyDirty();
    }


    public function getDescriptionForEvent(string $eventName): string
    {
        return "Server {$eventName}";
    }
}
