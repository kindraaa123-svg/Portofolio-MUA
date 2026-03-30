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
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-bg min-h-screen text-slate-800">
<div class="min-h-screen grid lg:grid-cols-[300px_1fr]">
    <aside class="bg-slate-950 text-white p-6 space-y-6">
        <a href="{{ route('admin.dashboard') }}" class="text-xl font-semibold">Selamat Datang, {{ auth()->user()?->name }}</a>
        <nav class="space-y-2 text-sm">
            @if(auth()->user()?->hasPermission('dashboard.view'))<a class="admin-link" href="{{ route('admin.dashboard') }}">Beranda Admin</a>@endif
            @if(auth()->user()?->hasPermission('portfolio.view'))<a class="admin-link" href="{{ route('admin.portfolios.index') }}">Portfolio</a>@endif
            @if(auth()->user()?->hasPermission('service.view'))<a class="admin-link" href="{{ route('admin.services.index') }}">Daftar Harga</a>@endif
            @if(auth()->user()?->hasPermission('booking.view'))<a class="admin-link" href="{{ route('admin.bookings.index') }}">Reservasi</a>@endif
            @if(auth()->user()?->hasPermission('report.view'))<a class="admin-link" href="{{ route('admin.reports.index') }}">Laporan</a>@endif
            @if(auth()->user()?->hasPermission('backup.view'))<a class="admin-link" href="{{ route('admin.backup.index') }}">Backup Database</a>@endif
            @if(auth()->user()?->hasPermission('recycle.view'))<a class="admin-link" href="{{ route('admin.recycle-bin.index') }}">Recycle Bin</a>@endif
            @if(auth()->user()?->hasPermission('access.view'))<a class="admin-link" href="{{ route('admin.access.index') }}">Hak Akses</a>@endif
            @if(auth()->user()?->hasPermission('user.view'))<a class="admin-link" href="{{ route('admin.users.index') }}">User Data</a>@endif
            @if(auth()->user()?->hasPermission('activity.view'))<a class="admin-link" href="{{ route('admin.activity-logs.index') }}">Log Aktivitas</a>@endif
            @if(auth()->user()?->hasPermission('setting.view'))<a class="admin-link" href="{{ route('admin.settings.index') }}">Pengaturan Website</a>@endif
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="w-full rounded-lg bg-rose-500 px-4 py-2 font-medium">Keluar</button>
        </form>
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
<div id="modal-crop-gambar-global-admin" class="fixed inset-0 z-[70] hidden bg-black/70 p-4">
    <div class="mx-auto mt-4 max-w-4xl rounded-2xl bg-white p-4">
        <h3 class="mb-3 text-lg font-semibold">Crop Gambar</h3>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-2">
            <img id="crop-gambar-global-admin" src="" class="max-h-[65vh] w-full object-contain" alt="Crop gambar">
        </div>
        <div class="mt-4 flex flex-wrap justify-end gap-2">
            <button type="button" class="btn-secondary" id="crop-lewati-admin">Lewati</button>
            <button type="button" class="btn-secondary" id="crop-batal-admin">Batal</button>
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

    if (!modal || !window.Cropper) {
        return;
    }

    let cropper = null;
    let currentInput = null;
    let queue = [];
    let croppedFiles = [];
    let currentIndex = 0;
    let busy = false;

    const closeModal = () => {
        modal.classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        cropImage.src = '';
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
            modal.classList.remove('hidden');

            if (cropper) {
                cropper.destroy();
            }

            cropper = new Cropper(cropImage, {
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                dragMode: 'move',
            });
        };
        reader.readAsDataURL(file);
    };

    const nextImage = () => {
        currentIndex += 1;
        loadCurrentImage();
    };

    const cropCurrentImage = () => {
        const source = queue[currentIndex];
        if (!source || !cropper) {
            nextImage();
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            maxWidth: 2200,
            maxHeight: 2200,
            imageSmoothingQuality: 'high',
        });

        canvas.toBlob((blob) => {
            if (!blob) {
                croppedFiles.push(source);
                nextImage();
                return;
            }

            const fileType = source.type || 'image/png';
            const extension = fileType.includes('jpeg') ? 'jpg' : (fileType.split('/')[1] || 'png');
            const baseName = source.name.replace(/\.[^.]+$/, '');
            const croppedFile = new File([blob], `${baseName}-crop.${extension}`, { type: fileType });
            croppedFiles.push(croppedFile);
            nextImage();
        }, source.type || 'image/png', 0.95);
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

    btnSimpan.addEventListener('click', cropCurrentImage);
    btnLewati.addEventListener('click', skipCurrentImage);
    btnBatal.addEventListener('click', () => {
        busy = false;
        closeModal();
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
