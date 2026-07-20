@extends('frontend.layouts.app')

@section('title', 'KYC Verification - Kumaranbooking')

@push('styles')
    <style>
        :root {
            --kb-primary: #4e54c8;
            --kb-secondary: #8f94fb;
            --kb-dark: #1e293b;
            --kb-light: #f8fafc;
            --kb-border: #e2e8f0;
        }

        .kb-premium-section {
            padding: 50px 0;
            background-color: #ffffff; /* Strict white background */
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
        }

        .kb-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04), 0 2px 10px rgba(0, 0, 0, 0.02);
            border: 1px solid var(--kb-border);
            max-width: 950px;
            margin: 0 auto;
            width: 100%;
        }

        .kb-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .kb-header h2 { 
            color: var(--kb-dark); 
            font-weight: 800; 
            letter-spacing: -0.5px;
            margin-bottom: 12px;
            font-size: 2rem;
        }
        
        .kb-header p { 
            color: #64748b; 
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* --- Grid Layouts --- */
        .kb-form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        .full-width { grid-column: span 2; }
        
        .kb-form-group label {
            display: block;
            font-weight: 700;
            color: var(--kb-dark);
            margin-bottom: 10px;
            font-size: 1.05rem;
        }

        /* --- Interactive Upload Box --- */
        .kb-upload-box {
            background: var(--kb-light);
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            height: 180px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .kb-upload-box:hover {
            border-color: var(--kb-primary);
            background: #f0f5ff;
        }

        /* The invisible file input covering the box */
        .kb-upload-input {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        /* Placeholder UI (Icon & Text) */
        .kb-upload-placeholder {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .kb-upload-placeholder i {
            font-size: 2.5rem;
            color: var(--kb-primary);
            margin-bottom: 12px;
            opacity: 0.9;
            transition: transform 0.3s ease;
        }
        .kb-upload-box:hover .kb-upload-placeholder i { transform: scale(1.1); }
        .kb-upload-placeholder small { color: #64748b; font-weight: 500; font-size: 0.9rem; }

        /* Preview UI */
        .kb-upload-preview {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 5;
            background: #fff;
            border-radius: 14px;
        }

        .kb-upload-preview img {
            width: 100%; height: 100%;
            object-fit: cover;
            border-radius: 14px;
        }

        /* Hover Overlay for Preview */
        .kb-preview-overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            opacity: 0;
            transition: all 0.3s ease;
            border-radius: 14px;
        }
        .kb-upload-preview:hover .kb-preview-overlay { opacity: 1; }

        .kb-action-btn {
            background: #ffffff;
            border: none;
            width: 45px; height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--kb-dark);
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: transform 0.2s, color 0.2s;
        }
        .kb-action-btn:hover { transform: scale(1.15); color: var(--kb-primary); }

        /* --- Fetched Documents Grid (Read-Only) --- */
        .kb-fetched-doc {
            position: relative;
            height: 200px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--kb-border);
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .kb-fetched-doc img {
            width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;
        }
        .kb-fetched-doc:hover img { transform: scale(1.05); }
        .kb-fetched-overlay {
            position: absolute; bottom: 0; left: 0; width: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 20px 15px 15px;
            color: #fff; display: flex; justify-content: space-between; align-items: flex-end;
        }
        .kb-fetched-overlay span { font-weight: 600; font-size: 1.05rem; }
        .kb-fetched-doc:hover .kb-view-icon { color: var(--kb-secondary); }

        /* --- Buttons & Alerts --- */
        .kb-btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--kb-primary) 0%, var(--kb-secondary) 100%);
            color: #ffffff; border: none; padding: 1.2rem;
            font-size: 1.15rem; font-weight: 700; border-radius: 14px;
            cursor: pointer; margin-top: 40px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(78, 84, 200, 0.25);
        }
        .kb-btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(78, 84, 200, 0.4); }

        .kb-alert {
            border-radius: 12px; padding: 16px 20px; border: none;
            margin-bottom: 30px; display: flex; align-items: center; gap: 12px; font-weight: 500;
        }
        .kb-alert-success { background: #ecfdf5; color: #065f46; box-shadow: 0 4px 12px rgba(16,185,129,0.1); }
        .kb-alert-danger { background: #fef2f2; color: #991b1b; box-shadow: 0 4px 12px rgba(239,68,68,0.1); }

        /* Status Banners */
        .kb-status-banner {
            border-radius: 20px; padding: 40px 30px; text-align: center; border: 2px solid transparent; margin-bottom: 40px;
        }
        .banner-approved { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
        .banner-pending { background: #fffbeb; border-color: #fef08a; color: #854d0e; }
        .kb-status-banner i { font-size: 4rem; margin-bottom: 20px; }
        .kb-status-banner h3 { font-weight: 800; font-size: 1.8rem; margin-bottom: 12px; }

        /* --- Lightbox Modal --- */
        .kb-modal {
            display: none; position: fixed; z-index: 9999; top: 0; left: 0;
            width: 100%; height: 100%; background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(5px); opacity: 0; transition: opacity 0.3s ease;
            align-items: center; justify-content: center;
        }
        .kb-modal.show { display: flex; opacity: 1; }
        .kb-modal-content {
            max-width: 90%; max-height: 85vh; border-radius: 16px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.5);
            transform: scale(0.95); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .kb-modal.show .kb-modal-content { transform: scale(1); }
        .kb-modal-close {
            position: absolute; top: 25px; right: 35px;
            color: #ffffff; font-size: 40px; font-weight: 300;
            cursor: pointer; transition: transform 0.3s;
            line-height: 1; text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        .kb-modal-close:hover { transform: scale(1.2); color: var(--kb-secondary); }

        @media (max-width: 768px) {
            .kb-premium-section { padding: 30px 15px; }
            .kb-card { padding: 30px 20px; border-radius: 20px; }
            .kb-form-grid { grid-template-columns: 1fr; gap: 20px; }
            .full-width { grid-column: span 1; }
            .kb-header h2 { font-size: 1.7rem; }
        }
    </style>
@endpush

@section('content')
    <section class="kb-premium-section">
        <div class="container">
            <div class="kb-card">
                
                <div class="kb-header">
                    <h2><i class="fas fa-user-shield text-primary mr-2"></i> Identity Verification</h2>
                    <p>Securely upload and manage your official documents to complete your profile verification.</p>
                </div>

                @if(session('success'))
                    <div class="kb-alert kb-alert-success">
                        <i class="fas fa-check-circle fa-lg"></i> 
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="kb-alert kb-alert-danger">
                        <i class="fas fa-exclamation-circle fa-lg"></i>
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- --- FETCHED & SUBMITTED STATE --- --}}
                @if($kyc && in_array($kyc->status, ['pending', 'approved']))
                    
                    @if($kyc->status === 'approved')
                        <div class="kb-status-banner banner-approved">
                            <i class="fas fa-shield-check text-success"></i>
                            <h3>KYC Approved</h3>
                            <p class="mb-0">Your identity has been successfully verified. Your account has full access to all features.</p>
                        </div>
                    @else
                        <div class="kb-status-banner banner-pending">
                            <i class="fas fa-user-clock text-warning"></i>
                            <h3>Verification Pending</h3>
                            <p class="mb-0">We have received your documents. Our team is currently reviewing them. This usually takes 24-48 hours.</p>
                        </div>
                    @endif

                    <!-- Display Uploaded Documents Grid -->
                    <h4 class="mb-4" style="font-weight: 700; color: var(--kb-dark);"><i class="fas fa-folder-open text-primary mr-2"></i> Submitted Documents</h4>
                    <div class="kb-form-grid">
                        @php
                            $docs = [
                                'Aadhar Card (Front)' => $kyc->aadhar_front,
                                'Aadhar Card (Back)' => $kyc->aadhar_back,
                                'PAN Card (Front)' => $kyc->pan_front,
                                'PAN Card (Back)' => $kyc->pan_back,
                                'Passport Size Photo' => $kyc->photo
                            ];
                        @endphp

                        @foreach($docs as $label => $path)
                            <div class="kb-fetched-doc {{ $label == 'Passport Size Photo' ? 'full-width' : '' }}" onclick="openModal('{{ asset($path) }}')">
                                <img src="{{ asset($path) }}" alt="{{ $label }}">
                                <div class="kb-fetched-overlay">
                                    <span>{{ $label }}</span>
                                    <i class="fas fa-expand-alt fa-lg kb-view-icon"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>

                {{-- --- FORM UPLOAD STATE --- --}}
                @else
                    
                    @if($kyc && $kyc->status === 'rejected')
                        <div class="kb-alert kb-alert-danger">
                            <i class="fas fa-exclamation-triangle fa-lg"></i> 
                            <div><strong>Verification Failed:</strong> Your previous request was rejected. Please upload clear, glare-free, and legible photos.</div>
                        </div>
                    @endif

                    <form action="{{ route('customer.kyc.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="kb-form-grid">
                            
                            <!-- Aadhar Front -->
                            <div class="kb-form-group">
                                <label>Aadhar Card (Front)</label>
                                <div class="kb-upload-box" id="box_aadhar_front">
                                    <input type="file" name="aadhar_front" id="input_aadhar_front" class="kb-upload-input" accept="image/*" required onchange="handleFilePreview(this, 'aadhar_front')">
                                    <div class="kb-upload-placeholder" id="placeholder_aadhar_front">
                                        <i class="fas fa-address-card"></i>
                                        <small>Tap to upload (Max 2MB)</small>
                                    </div>
                                    <div class="kb-upload-preview d-none" id="preview_container_aadhar_front">
                                        <img src="" id="img_aadhar_front" alt="Preview">
                                        <div class="kb-preview-overlay">
                                            <button type="button" class="kb-action-btn" title="View" onclick="openModal(document.getElementById('img_aadhar_front').src)"><i class="fas fa-expand-alt"></i></button>
                                            <button type="button" class="kb-action-btn" title="Change" onclick="triggerInput('input_aadhar_front')"><i class="fas fa-pen"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Aadhar Back -->
                            <div class="kb-form-group">
                                <label>Aadhar Card (Back)</label>
                                <div class="kb-upload-box" id="box_aadhar_back">
                                    <input type="file" name="aadhar_back" id="input_aadhar_back" class="kb-upload-input" accept="image/*" required onchange="handleFilePreview(this, 'aadhar_back')">
                                    <div class="kb-upload-placeholder" id="placeholder_aadhar_back">
                                        <i class="far fa-address-card"></i>
                                        <small>Tap to upload (Max 2MB)</small>
                                    </div>
                                    <div class="kb-upload-preview d-none" id="preview_container_aadhar_back">
                                        <img src="" id="img_aadhar_back" alt="Preview">
                                        <div class="kb-preview-overlay">
                                            <button type="button" class="kb-action-btn" title="View" onclick="openModal(document.getElementById('img_aadhar_back').src)"><i class="fas fa-expand-alt"></i></button>
                                            <button type="button" class="kb-action-btn" title="Change" onclick="triggerInput('input_aadhar_back')"><i class="fas fa-pen"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PAN Front -->
                            <div class="kb-form-group">
                                <label>PAN Card (Front)</label>
                                <div class="kb-upload-box" id="box_pan_front">
                                    <input type="file" name="pan_front" id="input_pan_front" class="kb-upload-input" accept="image/*" required onchange="handleFilePreview(this, 'pan_front')">
                                    <div class="kb-upload-placeholder" id="placeholder_pan_front">
                                        <i class="fas fa-id-badge"></i>
                                        <small>Tap to upload (Max 2MB)</small>
                                    </div>
                                    <div class="kb-upload-preview d-none" id="preview_container_pan_front">
                                        <img src="" id="img_pan_front" alt="Preview">
                                        <div class="kb-preview-overlay">
                                            <button type="button" class="kb-action-btn" title="View" onclick="openModal(document.getElementById('img_pan_front').src)"><i class="fas fa-expand-alt"></i></button>
                                            <button type="button" class="kb-action-btn" title="Change" onclick="triggerInput('input_pan_front')"><i class="fas fa-pen"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PAN Back -->
                            <div class="kb-form-group">
                                <label>PAN Card (Back)</label>
                                <div class="kb-upload-box" id="box_pan_back">
                                    <input type="file" name="pan_back" id="input_pan_back" class="kb-upload-input" accept="image/*" required onchange="handleFilePreview(this, 'pan_back')">
                                    <div class="kb-upload-placeholder" id="placeholder_pan_back">
                                        <i class="far fa-id-badge"></i>
                                        <small>Tap to upload (Max 2MB)</small>
                                    </div>
                                    <div class="kb-upload-preview d-none" id="preview_container_pan_back">
                                        <img src="" id="img_pan_back" alt="Preview">
                                        <div class="kb-preview-overlay">
                                            <button type="button" class="kb-action-btn" title="View" onclick="openModal(document.getElementById('img_pan_back').src)"><i class="fas fa-expand-alt"></i></button>
                                            <button type="button" class="kb-action-btn" title="Change" onclick="triggerInput('input_pan_back')"><i class="fas fa-pen"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Photo -->
                            <div class="kb-form-group full-width">
                                <label>Recent Passport Size Photo</label>
                                <div class="kb-upload-box" id="box_photo" style="max-width: 400px; margin: 0 auto;">
                                    <input type="file" name="photo" id="input_photo" class="kb-upload-input" accept="image/*" required onchange="handleFilePreview(this, 'photo')">
                                    <div class="kb-upload-placeholder" id="placeholder_photo">
                                        <i class="fas fa-camera-retro"></i>
                                        <small>Clear face with plain background</small>
                                    </div>
                                    <div class="kb-upload-preview d-none" id="preview_container_photo">
                                        <img src="" id="img_photo" alt="Preview">
                                        <div class="kb-preview-overlay">
                                            <button type="button" class="kb-action-btn" title="View" onclick="openModal(document.getElementById('img_photo').src)"><i class="fas fa-expand-alt"></i></button>
                                            <button type="button" class="kb-action-btn" title="Change" onclick="triggerInput('input_photo')"><i class="fas fa-pen"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="kb-btn-submit">
                            <i class="fas fa-lock mr-2"></i> Submit Documents Securely
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <!-- Fullscreen Lightbox Modal -->
    <div id="kbImageModal" class="kb-modal" onclick="closeModal()">
        <span class="kb-modal-close" onclick="closeModal()">&times;</span>
        <!-- Stop propagation so clicking the image doesn't close the modal -->
        <img class="kb-modal-content" id="kbModalImg" onclick="event.stopPropagation()">
    </div>
@endsection

@push('scripts')
    <script>
        // Smoothly Handle Local File Selection & Preview
        function handleFilePreview(input, key) {
            const file = input.files[0];
            const placeholder = document.getElementById('placeholder_' + key);
            const previewContainer = document.getElementById('preview_container_' + key);
            const imgElement = document.getElementById('img_' + key);

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgElement.src = e.target.result;
                    placeholder.classList.add('d-none');
                    previewContainer.classList.remove('d-none');
                    // Hide the file input so overlay buttons are clickable
                    input.style.display = 'none'; 
                }
                reader.readAsDataURL(file);
            }
        }

        // Programmatically trigger the hidden file input when "Edit" is clicked
        function triggerInput(inputId) {
            const input = document.getElementById(inputId);
            input.style.display = 'block'; // Temporarily show to allow click
            input.click();
            input.style.display = 'none'; // Hide again immediately
        }

        // Lightbox Modal Logic
        const modal = document.getElementById("kbImageModal");
        const modalImg = document.getElementById("kbModalImg");

        function openModal(imageSrc) {
            modalImg.src = imageSrc;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeModal() {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto'; // Restore scrolling
            // Clear src after fade out to prevent flashing next time
            setTimeout(() => { modalImg.src = ""; }, 300);
        }

        // Allow closing modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape" && modal.classList.contains('show')) {
                closeModal();
            }
        });
    </script>
@endpush