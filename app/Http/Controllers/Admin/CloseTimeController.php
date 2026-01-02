<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CloseTime;

class CloseTimeController extends Controller
{
    public function edit()
    {
        $closeTime = CloseTime::first();
        return view('admin.close_time.edit', compact('closeTime'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'minutes' => 'required|integer|min:0',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);
        $closeTime = CloseTime::first();
        if (!$closeTime) {
            $closeTime = CloseTime::create($validated);
        } else {
            $closeTime->update($validated);
        }
        return redirect()->route('admin.close-time.edit')->with('success', 'Close time and WhatsApp number updated successfully.');
    }
}
