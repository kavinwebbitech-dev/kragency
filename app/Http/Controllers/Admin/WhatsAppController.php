<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhatsAppLink;

class WhatsAppController extends Controller
{
    public function index()
    {
        // Always use first row
        $data = WhatsAppLink::first();
        return view('admin.whatsapp-link', compact('data'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'link' => 'required|string'
        ]);

        WhatsAppLink::updateOrCreate(
            ['id' => 1],
            ['link' => $request->link]
        );

        return back()->with('success', 'WhatsApp link updated successfully!');
    }
}
