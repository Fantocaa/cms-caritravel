<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Post extends Model
{
    use AsSource, Attachable, Filterable, HasFactory, SoftDeletes;

    public function country()
    {
        return $this->belongsTo(countries::class, 'countries');
    }

    public function city()
    {
        return $this->belongsTo(cities::class, 'cities');
    }

    public function setDateAttribute($value)
    {
        $this->attributes['start_date'] = $value['start'];
        $this->attributes['end_date'] = $value['end'];
    }

    public function getDateAttribute()
    {
        return [
            'start' => $this->start_date,
            'end' => $this->end_date,
        ];
    }

    protected $fillable = [
        'countries',
        'cities',
        'traveler',
        'duration',
        'date',
        'general_info',
        'travel_schedule',
        'additional_info',
        'title',
        'description',
        'body',
        'author',
    ];
}
