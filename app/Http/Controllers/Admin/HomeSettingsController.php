<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use App\Models\Course;

class HomeSettingsController extends Controller
{
    public function edit()
    {
        // Ensure there is always exactly one settings row
        $settings = SiteSetting::firstOrCreate([], [
            'hero_background_color' => '#4f46e5',
            'featured_course_ids'   => [],
        ]);

        $courses = Course::all();

        return view('admin.home.edit', compact('settings', 'courses'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_heading'          => 'nullable|string|max:255',
            'hero_subheading'       => 'nullable|string|max:255',
            'hero_button_text'      => 'nullable|string|max:255',
            // allow internal or external URLs, so keep as string instead of strict `url`
            'hero_button_link'      => 'nullable|string|max:255',
            'hero_background_color' => 'nullable|string|max:20',
            'featured_course_ids'   => 'nullable|array',
            'featured_course_ids.*' => 'integer|exists:courses,id',
        ]);

        // `featured_course_ids` is cast to array on the model, so just ensure it's an array
        $data['featured_course_ids'] = $data['featured_course_ids'] ?? [];

        $settings = SiteSetting::firstOrCreate([]);
        $settings->update($data);

        return back()->with('success', 'Home settings updated!');
    }
}
