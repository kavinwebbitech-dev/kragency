<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Services\ResultCalculationService;
use App\Models\ScheduleProvider;

class PublishResultController extends Controller
{
    //

    public function publishResult()
    {
    $today = now()->startOfDay();
    $orders = \App\Models\CustomerOrderItemModel::whereDate('created_at', $today)->get();

    $todayTotalOrders = $orders->count();
    $todayTotalAmount = $orders->sum('amount');
    $todayWinningAmount = $orders->sum('win_amount');
    $todayProfit = $todayTotalAmount - $todayWinningAmount;

    return view('admin.results.publish-results', compact('todayTotalOrders', 'todayTotalAmount', 'todayWinningAmount', 'todayProfit'));
    }

    public function viewAllResults()
    {
        return view('admin.results.view-all-results');
    }

    public function getTableData(Request $request)
    {
        if ($request->ajax()) {
            // Get data from schedule_provider joined with betting_providers and provider_slots, only non-deleted
            $results = DB::table('schedule_provider')
                ->leftJoin('betting_providers', 'schedule_provider.betting_providers_id', '=', 'betting_providers.id')
                ->leftJoin('provider_times', 'provider_times.id', '=', 'schedule_provider.slot_time_id')
                ->select(
                    'schedule_provider.*',
                    'betting_providers.name as provider_name',
                    'provider_times.time as slot_time'
                )
                ->whereNull('schedule_provider.deleted_at')
                ->whereDate('schedule_provider.created_at', '=', now()->toDateString())
                ->get();

            return DataTables::of($results)->make(true);
        }
    }

    public function viewAllResultsData(Request $request)
    {
        if ($request->ajax()) {
            // Get data from schedule_provider joined with betting_providers and provider_slots, only non-deleted
            $results = DB::table('schedule_provider')
                ->leftJoin('betting_providers', 'schedule_provider.betting_providers_id', '=', 'betting_providers.id')
                ->leftJoin('provider_times', 'provider_times.id', '=', 'schedule_provider.slot_time_id')
                ->select(
                    'schedule_provider.*',
                    'betting_providers.name as provider_name',
                    'provider_times.time as slot_time'
                )
                ->whereNull('schedule_provider.deleted_at')
                ->whereDate('schedule_provider.created_at', '<', now()->toDateString())
                ->get();

            return DataTables::of($results)->make(true);
        }
    }

    public function updateResult(Request $request, $provider_id)
    {
        try {
            $providerDetails = DB::table('schedule_provider')->find($provider_id);

            if (!$providerDetails) {
                return redirect()->route('admin.publish-results')->with('error', 'Provider not found.');
            }

            // Prevent editing if result is already published
            if ($providerDetails->result && $providerDetails->result !== '') {
                return redirect()->route('admin.publish-results')->with('error', 'Result is already published and cannot be edited.');
            }

            // If method is post, update the result and process winnings
            if (request()->isMethod('post')) {                $request = request();
                $request->validate([
                    'name' => 'required|string|max:255',
                ]);

                $result = $request->input('name');

                // Update the result in schedule_provider
                DB::table('schedule_provider')
                    ->where('id', $provider_id)
                    ->update([
                        'result' => $result,
                        'updated_at' => now(),
                    ]);

                // Use the service for winnings calculation
                //dispatch(new \App\Jobs\CalculateWinningsJob($provider_id, $providerDetails, $result));
                $service = new ResultCalculationService();
                $service->calculateWinnings($provider_id, $providerDetails, $result);

                return redirect()->route('admin.publish-results')->with('success', 'Result updated and winnings calculated successfully.');
            }

            // Continue with your logic
            return view('admin.results.update-result', compact('providerDetails'));
        } catch (\Exception $e) {
            return redirect()->route('admin.publish-results')->with('error', 'Error retrieving provider: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $provider = ScheduleProvider::with('slotTimes')->findOrFail($id);
        $provider->slotTimes()->update(['deleted_at' => now()]);
        $provider->delete();

        return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
    }
}
