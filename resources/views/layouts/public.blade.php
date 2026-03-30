<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $globalSetting->site_name ?? 'MUA Portfolio')</title>
    <meta name="application-name" content="{{ $globalSetting->site_name ?? 'MUA Portfolio' }}">
    <meta name="description" content="@yield('meta_description', $globalSetting->meta_description ?? '')">
    @if(!empty($globalSetting?->favicon))
        <link rel="icon" href="{{ asset('storage/' . $globalSetting->favicon) }}">
    @endif
    <style>
        :root {
            --theme-primary: {{ $globalSetting->theme_primary ?? '#c05b7b' }};
            --theme-secondary: {{ $globalSetting->theme_secondary ?? '#fce7ef' }};
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-bg text-rose-950">
<header class="sticky top-0 z-50 backdrop-blur-md border-b border-rose-200/70 bg-rose-50/90">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3 font-serif text-2xl tracking-wide text-rose-800">
            @if(!empty($globalSetting?->logo))
                <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="Logo" class="h-10 w-10 rounded-xl object-cover">
            @endif
            <span>{{ $globalSetting->site_name ?? 'Aurora Beauty MUA' }}</span>
        </a>

        <button class="md:hidden btn-secondary" type="button" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')">Menu</button>

        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('portfolio.index') }}">Portfolio</a>
            <a href="{{ route('pricelist') }}">Daftar Harga</a>
            <a href="{{ route('gallery') }}">Galeri</a>
            <a href="{{ route('testimonials') }}">Testimoni</a>
            <a href="{{ route('faq') }}">FAQ</a>
            <a href="{{ route('contact') }}">Kontak</a>
            <a href="{{ route('booking.create') }}" class="btn-primary">Reservasi</a>
        </nav>
    </div>

    <nav id="mobile-nav" class="md:hidden hidden border-t border-rose-200 bg-white/95 px-4 py-4">
        <div class="grid gap-3 text-sm font-medium">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('portfolio.index') }}">Portfolio</a>
            <a href="{{ route('pricelist') }}">Daftar Harga</a>
            <a href="{{ route('gallery') }}">Galeri</a>
            <a href="{{ route('testimonials') }}">Testimoni</a>
            <a href="{{ route('faq') }}">FAQ</a>
            <a href="{{ route('contact') }}">Kontak</a>
            <a href="{{ route('booking.create') }}" class="btn-primary text-center">Reservasi</a>
        </div>
    </nav>
</header>

<main>
    @if (session('success'))
        <div class="container mx-auto px-4 mt-6">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
        </div>
    @endif
    @yield('content')
</main>

<footer class="mt-20 border-t border-rose-200 bg-rose-100/70">
    <div class="container mx-auto px-4 py-8 text-sm text-rose-900/80">
        <p>(c) {{ now()->year }} {{ $globalSetting->site_name ?? 'MUA Portfolio' }}. Hak cipta dilindungi.</p>
    </div>
</footer>
<div id="modal-crop-gambar-global-public" class="fixed inset-0 z-[70] hidden bg-black/70 p-4">
    <div class="mx-auto mt-4 max-w-4xl rounded-2xl bg-white p-4">
        <h3 class="mb-3 text-lg font-semibold text-slate-900">Crop Gambar</h3>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-2">
            <img id="crop-gambar-global-public" src="" class="max-h-[65vh] w-full object-contain" alt="Crop gambar">
        </div>
        <div class="mt-4 flex flex-wrap justify-end gap-2">
            <button type="button" class="btn-secondary" id="crop-lewati-public">Lewati</button>
            <button type="button" class="btn-secondary" id="crop-batal-public">Batal</button>
            <button type="button" class="btn-primary" id="crop-simpan-public">Gunakan Hasil Crop</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-crop-gambar-global-public');
    const cropImage = document.getElementById('crop-gambar-global-public');
    const btnSimpan = document.getElementById('crop-simpan-public');
    const btnLewati = document.getElementById('crop-lewati-public');
    const btnBatal = document.getElementById('crop-batal-public');

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
</script>
</body>
</html>
