<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Attachment\Attachable;
use Orchid\Attachment\Models\Attachment;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Post extends Model
{
    use AsSource, Attachable, Filterable, HasFactory, SoftDeletes;

    protected $casts = [
        'countries' => 'array',
        'cities' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Accessors & Mutators for country_ids and city_ids
    public function getCountryIdsAttribute()
    {
        return json_decode($this->attributes['countries'], true);
    }

    public function setCountryIdsAttribute($value)
    {
        $this->attributes['countries'] = json_encode($value);
    }

    public function getCityIdsAttribute()
    {
        return json_decode($this->attributes['cities'], true);
    }

    public function setCityIdsAttribute($value)
    {
        $this->attributes['cities'] = json_encode($value);
    }

    // Old
    public function country()
    {
        return $this->belongsTo(countries::class, 'countries');
    }

    public function city()
    {
        return $this->belongsTo(cities::class, 'cities');
    }

    public function getImageUrlAttribute()
    {
        // Assuming the first attachment is the image you want to display
        $image = $this->Attachment->first();

        return $image ? $image->url : null;
    }

    public function attachments()
    {
        return $this->morphToMany(Attachment::class, 'attachmentable');
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
        'start_date',
        'end_date',
        'general_info',
        'travel_schedule',
        'additional_info',
        'title',
        'author',
        'price',
    ];
}
