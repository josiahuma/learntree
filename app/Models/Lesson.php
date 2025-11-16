<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    //

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function quiz()
    {
        return $this->hasOne(\App\Models\Quiz::class);
    }

    protected $fillable = [
        'course_id',
        'title',
        'content',
        'video_url',
        'order',
    ];
}
