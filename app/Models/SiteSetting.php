<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'hero_heading',
        'hero_subheading',
        'hero_button_text',
        'hero_button_link',
        'hero_background_color',
        'featured_course_ids',
    ];

    protected $casts = [
        // Automatically store/load as JSON <-> array
        'featured_course_ids' => 'array',
    ];
}
