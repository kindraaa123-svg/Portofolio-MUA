<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') - {{ $globalSetting->site_name ?? 'MUA' }}</title>
    <style>
        :root {
            --theme-primary: {{ $globalSetting->theme_primary ?? '#c05b7b' }};
            --theme-secondary: {{ $globalSetting->theme_secondary ?? '#fce7ef' }};
        }

        .admin-sidebar {
            background-color: var(--theme-primary) !important;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(0, 0, 0, 0.12)) !important;
        }

        /* Force hover sidebar to always use selected secondary color */
        .admin-sidebar .admin-link:hover,
        .admin-sidebar .admin-link:focus-visible,
        .admin-sidebar .admin-link-active {
            background-color: var(--theme-secondary) !important;
            border-color: var(--theme-secondary) !important;
            box-shadow: inset 4px 0 0 var(--theme-primary), 0 8px 16px rgba(2, 6, 23, 0.18);
            transform: translateX(2px);
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-bg min-h-screen text-slate-800">
<div class="min-h-screen grid lg:grid-cols-[300px_1fr]">
    <aside
        id="admin-sidebar"
        class="admin-sidebar relative overflow-hidden text-white p-4 lg:p-5 flex flex-col lg:h-screen lg:sticky lg:top-0"
        style="background-color: {{ $globalSetting->theme_primary ?? '#c05b7b' }}; background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(0, 0, 0, 0.12));"
    >
        <div class="admin-sidebar-glow absolute inset-0"></div>

        <div class="relative z-10 admin-user-chip rounded-2xl p-3">
            <a href="{{ route('admin.dashboard') }}" class="block min-w-0">
                <div class="min-w-0">
                    <p class="text-xs text-white/75">Selamat Datang</p>
                    <p class="font-semibold truncate">{{ auth()->user()?->name }}</p>
                </div>
            </a>
        </div>

        <div class="relative z-10 flex-1 min-h-0 overflow-y-auto mt-4 pr-1 space-y-4">
            <div class="admin-menu-section space-y-2 text-sm">
                <p class="admin-menu-label">Menu Utama</p>
                @if(auth()->user()?->hasPermission('dashboard.view'))<a class="admin-link {{ request()->routeIs('admin.dashboard') ? 'admin-link-active' : '' }}" href="{{ route('admin.dashboard') }}"><span>Beranda Admin</span></a>@endif
                @if(auth()->user()?->hasPermission('portfolio.view'))<a class="admin-link {{ request()->routeIs('admin.portfolios.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.portfolios.index') }}"><span>Portfolio</span></a>@endif
                @if(auth()->user()?->hasPermission('service.view'))<a class="admin-link {{ request()->routeIs('admin.services.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.services.index') }}"><span>Daftar Harga</span></a>@endif
                @if(auth()->user()?->hasPermission('booking.verify-payment'))<a class="admin-link {{ request()->routeIs('admin.bookings.payment-validations') ? 'admin-link-active' : '' }}" href="{{ route('admin.bookings.payment-validations') }}"><span>Validasi Pembayaran</span></a>@endif
                @if(auth()->user()?->hasPermission('report.view'))<a class="admin-link {{ request()->routeIs('admin.reports.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.reports.index') }}"><span>Laporan</span></a>@endif
                @if(auth()->user()?->hasPermission('backup.view'))<a class="admin-link {{ request()->routeIs('admin.backup.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.backup.index') }}"><span>Backup Database</span></a>@endif
            </div>

            <div class="admin-menu-section space-y-2 text-sm">
                <p class="admin-menu-label">Manajemen</p>
                @if(auth()->user()?->hasPermission('recycle.view'))<a class="admin-link {{ request()->routeIs('admin.recycle-bin.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.recycle-bin.index') }}"><span>Recycle Bin</span></a>@endif
                @if(auth()->user()?->hasPermission('access.view'))<a class="admin-link {{ request()->routeIs('admin.access.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.access.index') }}"><span>Hak Akses</span></a>@endif
                @if(auth()->user()?->hasPermission('user.view'))<a class="admin-link {{ request()->routeIs('admin.users.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.users.index') }}"><span>User Data</span></a>@endif
                @if(auth()->user()?->hasPermission('activity.view'))<a class="admin-link {{ request()->routeIs('admin.activity-logs.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.activity-logs.index') }}"><span>Log Aktivitas</span></a>@endif
                @if(auth()->user()?->hasPermission('setting.view'))<a class="admin-link {{ request()->routeIs('admin.operational-hours.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.operational-hours.index') }}"><span>Jam Operasional</span></a>@endif
                @if(auth()->user()?->hasPermission('setting.view'))<a class="admin-link {{ request()->routeIs('admin.settings.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.settings.index') }}"><span>Pengaturan Website</span></a>@endif
            </div>
        </div>

        <div class="relative z-10 mt-4 pt-4 admin-sidebar-divider space-y-2">
            <a class="admin-link {{ request()->routeIs('admin.profile.*') ? 'admin-link-active' : '' }}" href="{{ route('admin.profile.index') }}"><span>Profile</span></a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn-admin-logout">Keluar</button>
            </form>
        </div>
    </aside>

    <section class="p-6 lg:p-8">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </section>
</div>
<div id="modal-crop-gambar-global-admin" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/65 p-2 md:p-4 backdrop-blur-[2px]">
    <div class="flex w-full max-w-6xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 md:p-6 shadow-2xl" style="max-height: 94vh;">
        <h3 class="text-base font-semibold text-slate-900">Crop Gambar</h3>
        <p class="mt-1 text-sm text-slate-500">Atur area foto, lalu simpan hasil crop.</p>
        <div class="mt-3 min-h-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 p-2 mx-auto" style="height: clamp(360px, 62vh, 760px);">
            <img id="crop-gambar-global-admin" src="" class="h-full max-h-full w-full object-contain" alt="Crop gambar">
        </div>
        <div class="mt-4 border-t border-slate-100 pt-3">
            <div class="mb-3">
                <div class="mb-1 flex items-center justify-between">
                    <label for="crop-zoom-admin" class="text-sm font-medium text-slate-700">Zoom</label>
                    <span id="crop-zoom-value-admin" class="text-xs text-slate-500">100%</span>
                </div>
                <input id="crop-zoom-admin" type="range" min="-0.8" max="8" step="0.05" value="0" class="w-full">
            </div>
        </div>
        <div class="relative z-20 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-3">
            <button type="button" class="btn-secondary" id="crop-batal-admin">Batal</button>
            <button type="button" class="btn-secondary" id="crop-lewati-admin">Lewati</button>
            <button type="button" class="btn-primary" id="crop-simpan-admin">Gunakan Hasil Crop</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-crop-gambar-global-admin');
    const cropImage = document.getElementById('crop-gambar-global-admin');
    const btnSimpan = document.getElementById('crop-simpan-admin');
    const btnLewati = document.getElementById('crop-lewati-admin');
    const btnBatal = document.getElementById('crop-batal-admin');
    const zoomInput = document.getElementById('crop-zoom-admin');
    const zoomValue = document.getElementById('crop-zoom-value-admin');
    const CROP_TEMPLATE = '<cropper-canvas background><cropper-image rotatable scalable translatable></cropper-image><cropper-shade theme-color="rgba(15,23,42,0.48)"></cropper-shade><cropper-handle action="select" plain></cropper-handle><cropper-selection initial-coverage="0.84" movable resizable zoomable><cropper-grid role="grid" bordered covered></cropper-grid><cropper-crosshair centered></cropper-crosshair><cropper-handle action="move" theme-color="rgba(255,255,255,0.38)"></cropper-handle><cropper-handle action="n-resize"></cropper-handle><cropper-handle action="e-resize"></cropper-handle><cropper-handle action="s-resize"></cropper-handle><cropper-handle action="w-resize"></cropper-handle><cropper-handle action="ne-resize"></cropper-handle><cropper-handle action="nw-resize"></cropper-handle><cropper-handle action="se-resize"></cropper-handle><cropper-handle action="sw-resize"></cropper-handle></cropper-selection></cropper-canvas>';

    if (!modal || !window.Cropper) {
        return;
    }

    let cropper = null;
    let currentInput = null;
    let queue = [];
    let croppedFiles = [];
    let currentIndex = 0;
    let busy = false;
    let cropperImageEl = null;
    let currentZoomLevel = 0;
    let currentImageMetrics = null;

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

    const setZoomLabel = (level) => {
        if (zoomValue) {
            zoomValue.textContent = `${Math.max(10, Math.round((1 + level) * 100))}%`;
        }
    };

    const resetZoomUi = () => {
        currentZoomLevel = 0;
        if (zoomInput) {
            zoomInput.value = '0';
        }
        setZoomLabel(0);
    };

    const applyZoomLevel = (nextLevel) => {
        if (!cropperImageEl || typeof cropperImageEl.$zoom !== 'function') {
            return;
        }
        const delta = nextLevel - currentZoomLevel;
        if (Math.abs(delta) < 0.001) {
            return;
        }
        cropperImageEl.$zoom(delta);
        currentZoomLevel = nextLevel;
        setZoomLabel(currentZoomLevel);
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        cropperImageEl = null;
        currentImageMetrics = null;
        cropImage.src = '';
        resetZoomUi();
    };

    const updatePreview = (input, file) => {
        const previewId = input.dataset.previewTarget;
        if (!previewId) return;

        const preview = document.getElementById(previewId);
        if (!preview) return;

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    };

    const applyFilesToInput = (input, files) => {
        const dt = new DataTransfer();
        files.forEach((file) => dt.items.add(file));
        input.files = dt.files;
        if (files[0]) {
            updatePreview(input, files[0]);
        }
    };

    const finishCropping = () => {
        if (currentInput && croppedFiles.length > 0) {
            applyFilesToInput(currentInput, croppedFiles);
        }

        currentInput = null;
        queue = [];
        croppedFiles = [];
        currentIndex = 0;
        busy = false;
        closeModal();
    };

    const loadCurrentImage = () => {
        const file = queue[currentIndex];
        if (!file) {
            finishCropping();
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            cropImage.src = e.target.result;
            const stage = cropImage.parentElement;

            cropImage.style.height = '100%';
            cropImage.style.width = '100%';
            cropImage.style.maxWidth = '100%';
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (cropper) {
                cropper.destroy();
            }

            const initCropper = () => {
                cropper = new Cropper(cropImage, {
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    dragMode: 'move',
                    minContainerHeight: 360,
                    minContainerWidth: 360,
                    zoomable: true,
                    template: CROP_TEMPLATE,
                });

                setTimeout(() => {
                    if (!cropper || typeof cropper.getCropperImage !== 'function') {
                        return;
                    }

                    cropperImageEl = cropper.getCropperImage();
                    if (!cropperImageEl) {
                        return;
                    }

                    const applyInitialZoom = () => {
                        if (typeof cropperImageEl.$resetTransform === 'function') {
                            cropperImageEl.$resetTransform();
                        }

                        if (typeof cropperImageEl.$center === 'function') {
                            cropperImageEl.$center('contain');
                        }

                        let targetLevel = 0.65;
                        if (currentImageMetrics) {
                            const containScale = Math.min(
                                currentImageMetrics.stageWidth / currentImageMetrics.naturalWidth,
                                currentImageMetrics.stageHeight / currentImageMetrics.naturalHeight
                            );
                            const coverScale = Math.max(
                                currentImageMetrics.stageWidth / currentImageMetrics.naturalWidth,
                                currentImageMetrics.stageHeight / currentImageMetrics.naturalHeight
                            );
                            const factor = containScale > 0 ? (coverScale / containScale) : 1;
                            targetLevel = clamp((factor - 1) + 0.2, -0.8, 8);
                        }

                        currentZoomLevel = 0;
                        applyZoomLevel(targetLevel);
                        if (zoomInput) {
                            zoomInput.value = String(currentZoomLevel);
                        }

                        const selection = typeof cropper.getCropperSelection === 'function' ? cropper.getCropperSelection() : null;
                        if (selection) {
                            selection.initialCoverage = 0.84;
                            if (typeof selection.$initSelection === 'function') {
                                selection.$initSelection(true, true);
                            }
                        }
                    };

                    if (typeof cropperImageEl.$ready === 'function') {
                        cropperImageEl.$ready(applyInitialZoom);
                    } else {
                        applyInitialZoom();
                    }
                }, 80);
            };

            const probe = new Image();
            probe.onload = () => {
                if (stage) {
                    const maxWidth = Math.min(Math.floor(window.innerWidth * 0.78), 1120);
                    const maxHeight = Math.min(Math.floor(window.innerHeight * 0.72), 760);
                    const ratio = probe.naturalWidth / Math.max(probe.naturalHeight, 1);

                    let stageWidth = maxWidth;
                    let stageHeight = stageWidth / ratio;

                    if (stageHeight > maxHeight) {
                        stageHeight = maxHeight;
                        stageWidth = stageHeight * ratio;
                    }

                    if (stageWidth < 420) {
                        stageWidth = 420;
                        stageHeight = stageWidth / ratio;
                    }

                    if (stageHeight < 260) {
                        stageHeight = 260;
                        stageWidth = Math.min(maxWidth, stageHeight * ratio);
                    }

                    stage.style.width = `${Math.round(stageWidth)}px`;
                    stage.style.height = `${Math.round(stageHeight)}px`;

                    currentImageMetrics = {
                        naturalWidth: probe.naturalWidth,
                        naturalHeight: probe.naturalHeight,
                        stageWidth,
                        stageHeight,
                    };
                }

                initCropper();
            };
            probe.src = e.target.result;
        };
        reader.readAsDataURL(file);
    };

    const nextImage = () => {
        currentIndex += 1;
        loadCurrentImage();
    };

    const getCanvasFromCropper = async () => {
        if (!cropper) {
            return null;
        }

        if (typeof cropper.getCroppedCanvas === 'function') {
            try {
                return cropper.getCroppedCanvas({
                    maxWidth: 2600,
                    maxHeight: 2600,
                    imageSmoothingQuality: 'high',
                });
            } catch (e) {
                // fallback to Cropper v2 API below
            }
        }

        if (typeof cropper.getCropperSelection === 'function') {
            const selection = cropper.getCropperSelection();
            if (selection && typeof selection.$toCanvas === 'function') {
                try {
                    return await selection.$toCanvas({
                        width: 1800,
                    });
                } catch (e) {
                    return null;
                }
            }
        }

        return null;
    };

    const cropCurrentImage = async () => {
        const source = queue[currentIndex];
        if (!source || !cropper) {
            nextImage();
            return;
        }

        btnSimpan.disabled = true;
        btnSimpan.classList.add('opacity-60', 'cursor-not-allowed');

        const canvas = await getCanvasFromCropper();
        if (!canvas || typeof canvas.toBlob !== 'function') {
            croppedFiles.push(source);
            btnSimpan.disabled = false;
            btnSimpan.classList.remove('opacity-60', 'cursor-not-allowed');
            nextImage();
            return;
        }

        const blob = await new Promise((resolve) => {
            canvas.toBlob(resolve, source.type || 'image/png', 0.95);
        });

        if (!blob) {
            croppedFiles.push(source);
            btnSimpan.disabled = false;
            btnSimpan.classList.remove('opacity-60', 'cursor-not-allowed');
            nextImage();
            return;
        }

        try {
            const fileType = source.type || 'image/png';
            const extension = fileType.includes('jpeg') ? 'jpg' : (fileType.split('/')[1] || 'png');
            const baseName = source.name.replace(/\.[^.]+$/, '');
            const croppedFile = new File([blob], `${baseName}-crop.${extension}`, { type: fileType });
            croppedFiles.push(croppedFile);
        } catch (e) {
            croppedFiles.push(source);
        }

        btnSimpan.disabled = false;
        btnSimpan.classList.remove('opacity-60', 'cursor-not-allowed');
        nextImage();
    };

    const skipCurrentImage = () => {
        const source = queue[currentIndex];
        if (source) {
            croppedFiles.push(source);
        }
        nextImage();
    };

    document.querySelectorAll('input[type="file"]').forEach((input) => {
        input.addEventListener('change', () => {
            if (busy) return;
            if (!input.files || input.files.length === 0) return;

            const imageFiles = Array.from(input.files).filter((file) => file.type.startsWith('image/'));
            if (imageFiles.length === 0) return;

            busy = true;
            currentInput = input;
            queue = imageFiles;
            croppedFiles = [];
            currentIndex = 0;
            loadCurrentImage();
        });
    });

    btnSimpan.addEventListener('click', (event) => {
        event.preventDefault();
        if (btnSimpan.disabled) {
            return;
        }
        cropCurrentImage();
    });
    btnLewati.addEventListener('click', skipCurrentImage);
    btnBatal.addEventListener('click', () => {
        busy = false;
        btnSimpan.disabled = false;
        btnSimpan.classList.remove('opacity-60', 'cursor-not-allowed');
        closeModal();
    });

    if (zoomInput) {
        const onZoomChange = () => {
            const value = Number(zoomInput.value || 0);
            if (Number.isFinite(value)) {
                applyZoomLevel(value);
            }
        };
        zoomInput.addEventListener('input', onZoomChange);
        zoomInput.addEventListener('change', onZoomChange);
    }

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            busy = false;
            btnSimpan.disabled = false;
            btnSimpan.classList.remove('opacity-60', 'cursor-not-allowed');
            closeModal();
        }
    });
});

(() => {
    const setGeoFields = (form, payload) => {
        ['_latitude', '_longitude', '_geo_location'].forEach((name) => {
            let input = form.querySelector(`input[name="${name}"]`);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                form.appendChild(input);
            }
            input.value = payload[name] || '';
        });
    };

    const cached = {
        _latitude: localStorage.getItem('_latitude') || '',
        _longitude: localStorage.getItem('_longitude') || '',
        _geo_location: localStorage.getItem('_geo_location') || '',
    };

    const applyToAllForms = () => {
        document.querySelectorAll('form').forEach((form) => setGeoFields(form, cached));
    };

    applyToAllForms();

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            cached._latitude = String(pos.coords.latitude);
            cached._longitude = String(pos.coords.longitude);
            cached._geo_location = 'Browser Geolocation';

            localStorage.setItem('_latitude', cached._latitude);
            localStorage.setItem('_longitude', cached._longitude);
            localStorage.setItem('_geo_location', cached._geo_location);
            applyToAllForms();
        });
    }
})();
</script>
</body>
</html>

