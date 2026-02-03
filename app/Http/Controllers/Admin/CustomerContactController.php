<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerContact;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CustomerContactController extends Controller
{
    public function index(Request $request){
        if ($request->ajax()) {
            $contacts = CustomerContact::query();

            return DataTables::of($contacts)
                ->addIndexColumn()
                ->make(true);
        }

        return view('admin.contacts.index');
    }

    public function deleteContact($id, Request $request)
    {
        $contact = CustomerContact::find($id);
        if (!$contact) {
            return response()->json(['message' => 'Contact not found.'], 404);
        }
        $contact->delete();
        return response()->json(['message' => 'Contact deleted successfully.']);
    }
}
