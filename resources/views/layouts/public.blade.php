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
<body class="site-bg text-slate-800 min-h-screen flex flex-col">
<header class="public-header sticky top-0 z-50 backdrop-blur-md">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="public-brand flex items-center gap-3 font-serif text-2xl tracking-wide">
            @if(!empty($globalSetting?->logo))
                <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="Logo" class="h-10 w-10 rounded-xl object-cover">
            @endif
            <span>{{ $globalSetting->site_name ?? 'Aurora Beauty MUA' }}</span>
        </a>

        <button class="md:hidden btn-secondary" type="button" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')">Menu</button>

        <nav class="public-nav hidden md:flex items-center gap-6 text-sm font-medium">
            <a class="{{ request()->routeIs('home') ? 'chip-active' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="{{ request()->routeIs('portfolio.*') ? 'chip-active' : '' }}" href="{{ route('portfolio.index') }}">Portfolio</a>
            <a class="{{ request()->routeIs('pricelist') ? 'chip-active' : '' }}" href="{{ route('pricelist') }}">Daftar Harga</a>
            <a class="{{ request()->routeIs('faq') ? 'chip-active' : '' }}" href="{{ route('faq') }}">FAQ</a>
            <a class="{{ request()->routeIs('contact') ? 'chip-active' : '' }}" href="{{ route('contact') }}">Kontak</a>
            <a href="{{ route('booking.create') }}" class="btn-primary">Reservasi</a>
        </nav>
    </div>

    <nav id="mobile-nav" class="public-mobile-nav md:hidden hidden px-4 py-4">
        <div class="grid gap-3 text-sm font-medium">
            <a class="{{ request()->routeIs('home') ? 'chip-active' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="{{ request()->routeIs('portfolio.*') ? 'chip-active' : '' }}" href="{{ route('portfolio.index') }}">Portfolio</a>
            <a class="{{ request()->routeIs('pricelist') ? 'chip-active' : '' }}" href="{{ route('pricelist') }}">Daftar Harga</a>
            <a class="{{ request()->routeIs('faq') ? 'chip-active' : '' }}" href="{{ route('faq') }}">FAQ</a>
            <a class="{{ request()->routeIs('contact') ? 'chip-active' : '' }}" href="{{ route('contact') }}">Kontak</a>
            <a href="{{ route('booking.create') }}" class="btn-primary text-center">Reservasi</a>
        </div>
    </nav>
</header>

<main class="flex-1">
    @if (session('success'))
        <div class="container mx-auto px-4 mt-6">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
        </div>
    @endif
    @yield('content')
</main>

<footer class="public-footer mt-20">
    <div class="container mx-auto px-4 py-8 text-sm text-slate-700">
        <p>(c) {{ now()->year }} {{ $globalSetting->site_name ?? 'MUA Portfolio' }}. Hak cipta dilindungi.</p>
    </div>
</footer>
<div id="modal-crop-gambar-global-public" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/65 p-2 md:p-4 backdrop-blur-[2px]">
    <div class="flex w-full max-w-6xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 md:p-6 shadow-2xl" style="max-height: 94vh;">
        <h3 class="text-base font-semibold text-slate-900">Crop Gambar</h3>
        <p class="mt-1 text-sm text-slate-500">Atur area foto, lalu simpan hasil crop.</p>
        <div class="mt-3 min-h-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 p-2 mx-auto" style="height: clamp(360px, 62vh, 760px);">
            <img id="crop-gambar-global-public" src="" class="h-full max-h-full w-full object-contain" alt="Crop gambar">
        </div>
        <div class="mt-4 border-t border-slate-100 pt-3">
            <div class="mb-3">
                <div class="mb-1 flex items-center justify-between">
                    <label for="crop-zoom-public" class="text-sm font-medium text-slate-700">Zoom</label>
                    <span id="crop-zoom-value-public" class="text-xs text-slate-500">100%</span>
                </div>
                <input id="crop-zoom-public" type="range" min="-0.8" max="8" step="0.05" value="0" class="w-full">
            </div>
        </div>
        <div class="relative z-20 flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-3">
            <button type="button" class="btn-secondary" id="crop-batal-public">Batal</button>
            <button type="button" class="btn-secondary" id="crop-lewati-public">Lewati</button>
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
    const zoomInput = document.getElementById('crop-zoom-public');
    const zoomValue = document.getElementById('crop-zoom-value-public');
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
        if (btnSimpan.disabled) return;
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
</script>
</body>
</html>

