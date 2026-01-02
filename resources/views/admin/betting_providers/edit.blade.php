@extends('layouts.app')
@section('title', __('customers/message.edit_providers'))
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">{{ __('customers/message.edit_providers') }}</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('customers/message.edit_providers') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card card-warning card-outline mb-4">
                        <div class="card-header"><div class="card-title">{{ __('customers/message.edit_providers') }}</div></div>

                        <form action="{{ route('admin.provider.edit', $provider->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="name" class="col-sm-2 col-form-label">Name<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="name"
                                            name="name"
                                            value="{{ old('name', $provider->name) }}"
                                            required
                                        />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="status" class="col-sm-2 col-form-label">Status<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <select class="form-select" id="status" name="status">
                                            <option value="1" {{ old('status', $provider->status) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $provider->status) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="is_default" class="col-sm-2 col-form-label">Is Default</label>
                                    <div class="col-sm-10">
                                        <input type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $provider->is_default) ? 'checked' : '' }} />
                                    </div>
                                </div>

                                {{-- Image Upload --}}
                                <div class="row mb-3">
                                    <label for="image" class="col-sm-2 col-form-label">
                                        Image <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-10">
                                        {{-- Show existing image if available --}}
                                        @if($provider->image)
                                            <div class="mb-2">
                                                <img src="{{ asset('../storage/app/public/' . $provider->image) }}" 
                                                    alt="Provider Image" 
                                                    class="img-thumbnail" 
                                                    style="max-width: 150px; height: auto;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" />
                                    </div>
                                </div>

                                {{-- Dynamic Time Rows --}}
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Times<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <div id="time-container">
                                            @foreach($provider->times as $time)
                                                @php
                                                    $carbon = \Carbon\Carbon::createFromFormat('H:i:s', $time->time);
                                                    $hour = $carbon->format('h');
                                                    $minute = $carbon->format('i');
                                                    $ampm = $carbon->format('A');
                                                @endphp
                                                <div class="input-group mb-2">
                                                    <input type="hidden" name="time_id[]" value="{{ $time->id }}">
                                                    <div class="d-flex align-items-center">
                                                        <select class="form-select hour-picker" style="width:auto;display:inline-block;" required>
                                                            @for($h=1;$h<=12;$h++)
                                                                <option value="{{ str_pad($h,2,'0',STR_PAD_LEFT) }}" {{ $hour == str_pad($h,2,'0',STR_PAD_LEFT) ? 'selected' : '' }}>{{ str_pad($h,2,'0',STR_PAD_LEFT) }}</option>
                                                            @endfor
                                                        </select>
                                                        :
                                                        <select class="form-select minute-picker" style="width:auto;display:inline-block;" required>
                                                            @for($m=0;$m<60;$m+=5)
                                                                <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}" {{ $minute == str_pad($m,2,'0',STR_PAD_LEFT) ? 'selected' : '' }}>{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
                                                            @endfor
                                                        </select>
                                                        <select class="form-select ampm-picker" style="width:auto;display:inline-block;" required>
                                                            <option value="AM" {{ $ampm == 'AM' ? 'selected' : '' }}>AM</option>
                                                            <option value="PM" {{ $ampm == 'PM' ? 'selected' : '' }}>PM</option>
                                                        </select>
                                                    </div>
                                                    <input type="hidden" name="time[]" class="time-24h" value="{{ $time->time }}">
                                                    <button type="button" class="btn btn-danger remove-time">X</button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-primary mt-2" id="add-time">+ Add Time</button>
                                        <small class="text-muted">Times are shown in 12-hour format but will be saved in 24-hour format.</small>
                                    </div>
                                </div>

                                {{-- Dynamic Slots Rows --}}
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Slots<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <div id="slot-container">
                                            @forelse($provider->providerSlot as $slot)
                                                <div class="input-group mb-2">
                                                    {{-- Keep track of existing record --}}
                                                    <input type="hidden" name="slot_id[]" value="{{ $slot->id }}">

                                                    {{-- Dropdown from master --}}
                                                    <select name="slots[]" class="form-select" required>
                                                        @foreach($master_slots as $m_slot)
                                                            <option value="{{ $m_slot->id }}" 
                                                                {{ $m_slot->id == $slot->slot_id ? 'selected' : '' }}>
                                                                {{ $m_slot->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    {{-- Price field --}}
                                                    <input type="number" name="prices[]" class="form-control"
                                                        value="{{ $slot->amount }}" min="0" required />

                                                    <input type="number" name="winning_prices[]" class="form-control"  value="{{ $slot->winning_amount }}" min="0" required />

                                                    {{-- Remove button --}}
                                                    <button type="button" class="btn btn-danger remove-slot">X</button>
                                                </div>
                                            @empty
                                                {{-- Default empty row if provider has no slots --}}
                                                <div class="input-group mb-2">
                                                    <input type="hidden" name="slot_id[]" value="">
                                                    <select name="slots[]" class="form-select" required>
                                                        @foreach($master_slots as $m_slot)
                                                            <option value="{{ $m_slot->id }}">{{ $m_slot->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="number" name="prices[]" class="form-control" placeholder="Price" min="0" required />
                                                    <input type="number" name="winning_prices[]" class="form-control" placeholder="Winning Price" min="0" required />
                                                    <button type="button" class="btn btn-danger remove-slot" style="display:none;">X</button>
                                                </div>
                                            @endforelse
                                        </div>

                                        <button type="button" class="btn btn-primary mt-2" id="add-slot">+ Add Slot</button>
                                    </div>
                                </div>

                            </div>
                    
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">Update</button>
                                <a href="{{ route('admin.provider.index') }}" class="btn float-end">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let container = document.getElementById('time-container');
        let addBtn = document.getElementById('add-time');

        // Add new row
        addBtn.addEventListener('click', function () {
            let div = document.createElement('div');
            div.classList.add('input-group', 'mb-2');
            div.innerHTML = `
                <input type="hidden" name="time_id[]" value="">
                <div class="d-flex align-items-center">
                    <select class="form-select hour-picker" style="width:auto;display:inline-block;" required>
                        ${Array.from({length:12},(_,i)=>`<option value='${String(i+1).padStart(2,'0')}'>${String(i+1).padStart(2,'0')}</option>`).join('')}
                    </select>
                    :
                    <select class="form-select minute-picker" style="width:auto;display:inline-block;" required>
                        ${Array.from({length:12},(_,i)=>`<option value='${String(i*5).padStart(2,'0')}'>${String(i*5).padStart(2,'0')}</option>`).join('')}
                    </select>
                    <select class="form-select ampm-picker" style="width:auto;display:inline-block;" required>
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                </div>
                <input type="hidden" name="time[]" class="time-24h" value="">
                <button type="button" class="btn btn-danger remove-time">X</button>
            `;
            container.appendChild(div);
        });

        // Remove row
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-time')) {
                e.target.closest('.input-group').remove();
            }
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
        // --- Slots section ---
        let slotContainer = document.getElementById('slot-container');
        let addSlotBtn = document.getElementById('add-slot');

        addSlotBtn.addEventListener('click', function () {
            let div = document.createElement('div');
            div.classList.add('input-group', 'mb-2');

            div.innerHTML = `
                <input type="hidden" name="slot_id[]" value="">
                <select name="slots[]" class="form-select" required>
                    @foreach($master_slots as $m_slot)
                        <option value="{{ $m_slot->id }}">{{ $m_slot->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="prices[]" class="form-control" placeholder="Price" min="0" required />
                <input type="number" name="winning_prices[]" class="form-control" placeholder="Winning Price" min="0" required />
                <button type="button" class="btn btn-danger remove-slot">X</button>
            `;

            slotContainer.appendChild(div);

            // remove functionality
            div.querySelector('.remove-slot').addEventListener('click', function () {
                div.remove();
            });
        });

        // apply remove handler to already loaded rows
        document.querySelectorAll('.remove-slot').forEach(btn => {
            btn.addEventListener('click', function () {
                btn.closest('.input-group').remove();
            });
        });
    });

</script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Convert 12h picker to 24h before submit
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                document.querySelectorAll('.input-group').forEach(function(group) {
                    const hourSel = group.querySelector('.hour-picker');
                    const minSel = group.querySelector('.minute-picker');
                    const ampmSel = group.querySelector('.ampm-picker');
                    const hidden = group.querySelector('.time-24h');
                    if (hourSel && minSel && ampmSel && hidden) {
                        let hour = parseInt(hourSel.value);
                        const min = minSel.value;
                        const ampm = ampmSel.value;
                        if (ampm === 'PM' && hour < 12) hour += 12;
                        if (ampm === 'AM' && hour === 12) hour = 0;
                        const hStr = hour.toString().padStart(2, '0');
                        hidden.value = `${hStr}:${min}:00`;
                    }
                });
            });
        }
    });
    </script>
@endpush
