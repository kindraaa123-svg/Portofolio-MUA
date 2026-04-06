<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') - {{ $globalSetting->site_name ?? 'MUA' }}</title>
    <style>
        :root {
            --theme-primary: #0f2747;
            --theme-secondary: #dbe8f7;
        }

        .admin-sidebar {
            background-color: var(--theme-primary) !important;
            background-image: none !important;
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

        @media (min-width: 1024px) {
            body.admin-layout {
                overflow: hidden;
            }

            .admin-shell {
                height: 100vh;
                overflow: hidden;
            }

            .admin-sidebar-panel {
                position: fixed;
                top: 0;
                left: 0;
                width: 300px;
                height: 100vh;
            }

            .admin-main {
                margin-left: 300px;
                height: 100vh;
                min-height: 0;
                overflow-y: auto;
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-layout admin-bg min-h-screen text-slate-800">
<div class="admin-shell min-h-screen">
    @include('layouts.partials.admin-sidebar')

    <section class="admin-main p-6 lg:p-8">
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
<script>
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

