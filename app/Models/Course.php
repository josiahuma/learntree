<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'sale_price',
        'featured_image',
        'difficulty',
        'duration',
        'is_approved',
    ];


    public function instructor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments');
    }

    public function enrollments()
    {
        return $this->hasMany(\App\Models\Enrollment::class);
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function averageRating()
    {
        return round($this->reviews()->avg('rating'), 1); // e.g., 4.3
    }






}
