<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\BettingProvidersModel; 
use App\Models\Admin\DigitMasterModel;
use App\Models\Admin\ProviderTimeModel;
use App\Models\Admin\BettingProviderSlotModel;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class BettingProviderController extends Controller
{
    public function index()
    {
        return view('admin.betting_providers.list');
    }

    public function getTableData(Request $request)
    {
        if ($request->ajax()) {
            $getProviders = BettingProvidersModel::get();
            return DataTables::of($getProviders)
                ->editColumn('status', function ($row) {
                    return $row->status == 1 ? 'Active' : 'Inactive';
                })
                ->editColumn('is_default', function ($row) {
                    return $row->is_default == 1 ? 'Yes' : 'No';
                })
                ->make(true);
        }
    }

    public function addProvider(Request $request) {


        $data['slots'] = DigitMasterModel::all();
        if ($request->isMethod('post')) {
            //do insert operation here
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'status' => ['required', 'in:0,1'],
                'times'  => ['required', 'array', 'min:1'],
                'times.*'=> ['required', 'distinct', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'], // 24-hour format
                'image'  => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // 2MB limit
            ]);

            if (count($request->times) !== count(array_unique($request->times))) {
                return back()->withErrors(['times' => 'Duplicate times are not allowed'])->withInput();
            }

            // Validation: If is_default is checked and a default already exists, show error
            if ($request->has('is_default')) {
                $existingDefault = BettingProvidersModel::where('is_default', 1)->first();
                if ($existingDefault) {
                    return back()->withErrors(['is_default' => 'A provider is already marked as default.'])->withInput();
                }
            }

            DB::beginTransaction();

            $imagePath = null;
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('providers', 'public');
            }

            try {
                if ($request->has('is_default')) {
                    // Set all others to not default
                    BettingProvidersModel::query()->update(['is_default' => 0]);
                }
                $provider = BettingProvidersModel::create([
                    'name' => $request->name,
                    'status' => $request->status,
                    'is_default' => $request->has('is_default') ? 1 : 0,
                    'image' => $imagePath,
                ]);

                // Save provider times
                foreach ($request->times as $time) {
                    ProviderTimeModel::create([
                        'betting_providers_id' => $provider->id,
                        'time' => $time,
                    ]);
                }

                 // Handle Slots
                $slots = $request->input('slots', []);   // slot IDs
                $prices = $request->input('prices', []); // amounts
                $winning_prices = $request->input('winning_prices', []);

                foreach ($slots as $index => $slotId) {
                    if (!empty($slotId) && isset($prices[$index])) {
                        BettingProviderSlotModel::create([
                            'betting_provider_id' => $provider->id,
                            'slot_id'             => $slotId,
                            'amount'              => (int) $prices[$index],
                            'winning_amount'      => (int) $winning_prices[$index],
                        ]);
                    }
                }

                DB::commit();
                
                Artisan::queue('app:schedule-daily-game');
                return redirect(route('admin.provider.index', [], false))->with('success', 'Provider created successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->withErrors(['error' => $e->getMessage()])->withInput();
            }
        }

        return view('admin.betting_providers.add', $data);
    }

    public function editProvider($provider_id, Request $request) {
        $provider = BettingProvidersModel::with(['times', 'providerSlot'])->find($provider_id);
        $master_slots = DigitMasterModel::all();
        if ($provider == null) {
            return redirect(route('admin.provider.index', [], false))->with('error', 'Provider not found.');
        }   

        // Update Method

    if ($request->isMethod('post')) {
            $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:betting_providers,name,' . $provider_id],
                'status' => ['required', 'in:0,1'],
                'time'   => ['required', 'array', 'min:1'],
                'time.*' => ['required', 'distinct', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'], // 24-hour format
                'time_id'=> 'array',
            ]);

            // Validation: If is_default is checked and a default already exists (not this provider), show error
            if ($request->has('is_default')) {
                $existingDefault = BettingProvidersModel::where('is_default', 1)->where('id', '!=', $provider_id)->first();
                if ($existingDefault) {
                    return back()->withErrors(['is_default' => 'A provider is already marked as default.'])->withInput();
                }
            }

            $imagePath = null;
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('providers', 'public');
            }

            // add update operation here
            DB::transaction(function () use ($request, $provider, $imagePath) {
                if ($request->has('is_default')) {
                    // Set all others to not default
                    BettingProvidersModel::where('id', '!=', $provider->id)->update(['is_default' => 0]);
                }
                $provider->update([
                    'name'   => $request->name,
                    'status' => $request->status,
                    'is_default' => $request->has('is_default') ? 1 : 0,
                    'image'  => $imagePath ?? $provider->image,
                ]);

                $processedIds = [];

                foreach ($request->time as $index => $timeValue) {
                    $timeId = $request->time_id[$index] ?? null;

                    if ($timeId) {
                        $time = $provider->times()->find($timeId);
                        if ($time) {
                            $time->update(['time' => $timeValue]);
                            $processedIds[] = $timeId;
                        }
                    } else {
                        $new = $provider->times()->create([
                            'time'   => $timeValue,
                            'status' => 1,
                        ]);
                        $processedIds[] = $new->id;
                    }
                }

                $provider->times()->whereNotIn('id', $processedIds)->delete();

                // for slots
                $slotIds = $request->input('slot_id', []);
                $slots   = $request->input('slots', []); 
                $prices  = $request->input('prices', []);
                $winning_prices = $request->input('winning_prices', []);

                $keepIds = []; // track updated/created ids

                foreach ($slots as $index => $slotValue) {
                    $dbId   = $slotIds[$index] ?? null;
                    $amount = $prices[$index] ?? null;
                    $winning_amount = $winning_prices[$index] ?? null;

                    if ($dbId) {
                        // 🔹 Update existing record
                        $provider->providerSlot()->where('id', $dbId)->update([
                            'slot_id' => $slotValue,
                            'amount'  => (int) $amount,
                            'winning_amount'  => (int) $winning_amount,
                        ]);
                        $keepIds[] = $dbId;
                    } else {
                        // 🔹 Create new record
                        $new = $provider->providerSlot()->create([
                            'slot_id' => $slotValue,
                            'amount'  => (int) $amount,
                            'winning_amount'  => (int) $winning_amount,
                        ]);
                        $keepIds[] = $new->id;
                    }
                }

                // Delete slots that were not submitted
                $provider->providerSlot()->whereNotIn('id', $keepIds)->delete();
                
                Artisan::queue('app:schedule-daily-game');
            });

            return redirect(route('admin.provider.index'))
                ->with('success', 'Provider updated successfully.');
        }
        return view('admin.betting_providers.edit', [
            'provider' => $provider,
            'provider_id' => $provider_id,
            'master_slots' => $master_slots,
        ]);
    }

    public function deleteProvider($provider_id, Request $request)
    {
        $provider = BettingProvidersModel::find($provider_id);
        if (!$provider) {
            return response()->json(['message' => 'Provider not found.'], 404);
        }
        $provider->delete();
        return response()->json(['message' => 'Provider deleted successfully.']);
    }
}
