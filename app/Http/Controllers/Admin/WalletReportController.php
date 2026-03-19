<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Admin\WalletTransactionLogModel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WalletReportController extends Controller
{

    public function index()
    {
        $users = User::select('id','name','email')->orderBy('name')->get();
        $admins = WalletTransactionLogModel::select('created_by')->groupBy('created_by')->get();

        return view('admin.wallet.report', compact('users','admins'));
    }


    private function query($request)
    {
        $query = WalletTransactionLogModel::with(['user','userDetail'])
        ->orderBy('id','desc');
        if ($request->user_id) {
            $query->where('user_id',$request->user_id);
        }

        if ($request->created_by) {
            $query->where('created_by',$request->created_by);
        }

        if ($request->from_date) {
            $query->whereDate('created_at','>=',$request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at','<=',$request->to_date);
        }

        return $query;
    }


    public function data(Request $request)
    {
        $query = $this->query($request);

        return DataTables::of($query)

            ->addColumn('user_info', function($row){
                return $row->user->name ?? '';
            })

            ->addColumn('created_by', function($row){
                return $row->userDetail->name ?? '';
            })

            ->addColumn('type_fmt', function($row){
                return ucfirst($row->type);
            })

            ->addColumn('amount_fmt', function($row){
                return number_format($row->amount,2);
            })

            ->addColumn('bonus_fmt', function($row){
                return number_format($row->bonus_amount,2);
            })

            ->addColumn('created_at_fmt', function($row){
                return date('d-m-Y H:i',strtotime($row->created_at));
            })

            ->rawColumns(['user_info'])

            ->make(true);
    }



    // public function summary(Request $request)
    // {
    //     $query = $this->query($request);

    //     $data = $query->select(
    //         DB::raw('COUNT(*) as total_records'),
    //         DB::raw('SUM(amount) as total_amount'),
    //         DB::raw('SUM(bonus_amount) as total_bonus'),
    //         DB::raw('MAX(amount) as max_amount')
    //     )->first();

    //     return response()->json($data);
    // }



    public function export(Request $request)
    {
        $records = $this->query($request)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1','ID');
        $sheet->setCellValue('B1','Created By');
        $sheet->setCellValue('C1','User');
        $sheet->setCellValue('D1','Type');
        $sheet->setCellValue('E1','Amount');
        $sheet->setCellValue('F1','Bonus');
        $sheet->setCellValue('G1','Description');
        $sheet->setCellValue('H1','Created At');

        $row = 2;

        foreach ($records as $i => $wallet) {

            $sheet->setCellValue('A'.$row, $i + 1);
            $sheet->setCellValue('B'.$row, $wallet->userDetail?->name ?? '');
            $sheet->setCellValue('C'.$row, $wallet->user->name ?? '');
            $sheet->setCellValue('D'.$row, $wallet->type);
            $sheet->setCellValue('E'.$row, $wallet->amount);
            $sheet->setCellValue('F'.$row, $wallet->bonus_amount);
            $sheet->setCellValue('G'.$row, $wallet->description);
            $sheet->setCellValue('H'.$row, $wallet->created_at);

            $row++;
        }

        $filename = "wallet-report.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
        exit;
    }

}