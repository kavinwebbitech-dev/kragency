<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;


class CustomerImportController extends Controller
{
    public function showImportForm()
    {
        return view('admin.users.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('import_file');
        $data = Excel::toArray(null, $file)[0];
        $header = array_map('strtolower', $data[0]);
        $imported = [];
        $failed = [];

        foreach (array_slice($data, 1) as $row) {
            $rowData = array_combine($header, $row);
            // Check required fields
            if (empty($rowData['name']) || empty($rowData['mobile']) || empty($rowData['password']) || empty($rowData['confirm_password'])) {
                $rowData['reason'] = 'Missing required fields';
                $failed[] = $rowData;
                continue;
            }
            // Check if mobile already exists
            $exists = \App\Models\User::where('mobile', $rowData['mobile'])->exists();
            if ($exists) {
                $rowData['reason'] = 'Mobile number already exists';
                $failed[] = $rowData;
                continue;
            }
            // Check password match
            if ($rowData['password'] !== $rowData['confirm_password']) {
                $rowData['reason'] = 'Password and Confirm Password do not match';
                $failed[] = $rowData;
                continue;
            }
            // Create user (no email)
            $user = User::create([
                'name' => $rowData['name'],
                'mobile' => $rowData['mobile'],
                'password' => Hash::make($rowData['password']),
                'status' => 1,
                'user_type' => 'normal',
            ]);
            $imported[] = $user;
        }
        return view('admin.users.import_result', compact('imported', 'failed'));
    }

    public function exportFailed(Request $request)
    {
        $failed = json_decode(base64_decode($request->input('failed_data')), true);
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="failed_customers.csv"',
        ];
        $callback = function() use ($failed) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Mobile', 'Password', 'Confirm Password', 'Reason']);
            foreach ($failed as $row) {
                fputcsv($out, [
                    $row['name'] ?? '-',
                    $row['mobile'] ?? '-',
                    $row['password'] ?? '-',
                    $row['confirm_password'] ?? '-',
                    $row['reason'] ?? 'Invalid',
                ]);
            }
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }
}
