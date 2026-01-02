@extends('layouts.app')
@section('title', __('customers/message.add_providers'))
@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">{{ __('customers/message.add_providers') }}</h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">List {{ __('customers/message.add_providers') }}</li>
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
                    <div class="card-header"><div class="card-title">{{ __('customers/message.add_providers') }}</div></div>
                        <form action="{{ route('admin.provider.add') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="name" class="col-sm-2 col-form-label">Name<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required />
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="status" class="col-sm-2 col-form-label">Status<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <select class="form-select" id="status" name="status">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="is_default" class="col-sm-2 col-form-label">Is Default</label>
                                    <div class="col-sm-10">
                                        <input type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }} />
                                    </div>
                                </div>

                                {{-- Image Upload --}}
                                <div class="row mb-3">
                                    <label for="image" class="col-sm-2 col-form-label">
                                        Image <span class="text-danger">*</span>
                                    </label>
                                    <div class="col-sm-10">
                                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required />
                                    </div>
                                </div>

                                {{-- Dynamic Time Rows --}}
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Time<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <div id="time-container">
                                            <div class="input-group mb-2">
                                                <div class="d-flex align-items-center">
                                                    <select class="form-select hour-picker" style="width:auto;display:inline-block;" required>
                                                        @for($h=1;$h<=12;$h++)
                                                            <option value="{{ str_pad($h,2,'0',STR_PAD_LEFT) }}">{{ str_pad($h,2,'0',STR_PAD_LEFT) }}</option>
                                                        @endfor
                                                    </select>
                                                    :
                                                    <select class="form-select minute-picker" style="width:auto;display:inline-block;" required>
                                                        @for($m=0;$m<60;$m+=5)
                                                            <option value="{{ str_pad($m,2,'0',STR_PAD_LEFT) }}">{{ str_pad($m,2,'0',STR_PAD_LEFT) }}</option>
                                                        @endfor
                                                    </select>
                                                    <select class="form-select ampm-picker" style="width:auto;display:inline-block;" required>
                                                        <option value="AM">AM</option>
                                                        <option value="PM">PM</option>
                                                    </select>
                                                </div>
                                                <input type="hidden" name="times[]" class="time-24h" value="">
                                                <button type="button" class="btn btn-danger remove-time" style="display:none;">&times;</button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm mt-2" id="add-time">+ Add Time</button>
                                        <small class="text-muted">Times are shown in 12-hour format but will be saved in 24-hour format.</small>
                                    </div>
                                </div>

                                {{-- Dynamic Slots Rows --}}
                                <div class="row mb-3">
                                    <label class="col-sm-2 col-form-label">Slots<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                        <div id="slot-container">
                                            <div class="input-group mb-2">
                                                {{-- Dropdown from master --}}
                                                <select name="slots[]" class="form-select" required>
                                                    @foreach($slots as $slot)
                                                        <option value="{{ $slot->id }}">{{ $slot->name }}</option>
                                                    @endforeach
                                                </select>

                                                {{-- Price integer --}}
                                                <input type="number" name="prices[]" class="form-control" placeholder="Price" min="0" required />

                                                {{-- winnig Price integer --}}
                                                <input type="number" name="winning_prices[]" class="form-control" placeholder="Winning Price" min="0" required />

                                                {{-- Remove button (hidden for first row) --}}
                                                <button type="button" class="btn btn-danger remove-slot" style="display:none;">&times;</button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm mt-2" id="add-slot">+ Add Slot</button>
                                    </div>
                                </div>


                            </div>
                    
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">Create</button>
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

        addBtn.addEventListener('click', function () {
            let div = document.createElement('div');
            div.classList.add('input-group', 'mb-2');
            div.innerHTML = `
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
                <input type="hidden" name="times[]" class="time-24h" value="">
                <button type="button" class="btn btn-danger remove-time">&times;</button>
            `;
            container.appendChild(div);
            div.querySelector('.remove-time').addEventListener('click', function () {
                div.remove();
            });
        });

        // 🔹 Slots logic
        let slotContainer = document.getElementById('slot-container');
        let addSlotBtn = document.getElementById('add-slot');

        addSlotBtn.addEventListener('click', function () {
            let div = document.createElement('div');
            div.classList.add('input-group', 'mb-2');

            div.innerHTML = `
                <select name="slots[]" class="form-select" required>
                    @foreach($slots as $slot)
                        <option value="{{ $slot->id }}">{{ $slot->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="prices[]" class="form-control" placeholder="Price" min="0" required />
                <input type="number" name="winning_prices[]" class="form-control" placeholder="Winning Price" min="0" required />
                <button type="button" class="btn btn-danger remove-slot">&times;</button>
            `;

            slotContainer.appendChild(div);

            div.querySelector('.remove-slot').addEventListener('click', function () {
                div.remove();
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