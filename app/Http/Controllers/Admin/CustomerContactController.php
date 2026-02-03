<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerContact;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export()
    {
        $fileName = 'customer_contacts_' . now()->format('Y_m_d_His') . '.csv';

        $contacts = CustomerContact::all();

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $callback = function () use ($contacts) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Name', 'Mobile Numbers', 'Emails']);

            foreach ($contacts as $contact) {

                $mobiles = collect($contact->mobile_numbers)
                    ->pluck('number')
                    ->implode(', ');

                $emails = collect($contact->emails)
                    ->implode(', ');

                fputcsv($file, [
                    $contact->name,
                    $mobiles,
                    $emails,
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
