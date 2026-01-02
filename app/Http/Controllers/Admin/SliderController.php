<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = \App\Models\Admin\SliderModel::orderBy('order')->get();
        return view('admin.slider.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.slider.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image_path' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url',
            'status' => 'boolean',
            'order' => 'integer',
        ]);

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time().'_'.$image->getClientOriginalName();
            $image->move(public_path('uploads/sliders'), $imageName);
            $validated['image_path'] = 'uploads/sliders/' . $imageName;
        }

    $validated['status'] = $request->has('status') ? $request->status : 0;

        \App\Models\Admin\SliderModel::create($validated);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider created successfully.');
    }
    public function edit($id)
    {
        $slider = \App\Models\Admin\SliderModel::findOrFail($id);
        return view('admin.slider.edit', compact('slider'));
    }

    public function update(Request $request, $id)
    {
        $slider = \App\Models\Admin\SliderModel::findOrFail($id);
        $validated = $request->validate([
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|url',
            'status' => 'boolean',
            'order' => 'integer',
        ]);

        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time().'_'.$image->getClientOriginalName();
            $image->move(public_path('uploads/sliders'), $imageName);
            $validated['image_path'] = 'uploads/sliders/' . $imageName;
        }

    $validated['status'] = $request->has('status') ? $request->status : 0;
        $slider->update($validated);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider updated successfully.');
    }

    public function destroy($id)
    {
        $slider = \App\Models\Admin\SliderModel::findOrFail($id);
        $slider->delete();
        return redirect()->route('admin.sliders.index')->with('success', 'Slider deleted successfully.');
    }
}
