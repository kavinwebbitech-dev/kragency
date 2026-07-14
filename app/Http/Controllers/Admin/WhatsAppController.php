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
        $contact = WhatsAppLink::where('id',2)->first();
        return view('admin.whatsapp-link', compact('data','contact'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'link' => 'nullable|string'
        ]);
        if($request->link){
            WhatsAppLink::updateOrCreate(
                ['id' => 1],
                ['link' => $request->link]
            );
            return back()->with('success', 'WhatsApp link updated successfully!');
        }
        if($request->emergency_contact){
            WhatsAppLink::updateOrCreate(
                ['id' => 2],
                ['link' => $request->emergency_contact]
            );
            return back()->with('success', 'Emergency Contact updated successfully!');
        }
    }
}