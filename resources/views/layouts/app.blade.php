<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NY Driving School - Dashboard')</title>
    
    <!-- Apply saved theme before first paint (avoids flash) -->
    <script>
        (function () {
            var theme = 'dark';
            try { theme = localStorage.getItem('nyds-theme') || 'dark'; } catch (e) {}
            document.documentElement.classList.add(theme === 'light' ? 'theme-light' : 'theme-dark');
        })();
    </script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // slate / amber / yellow are driven by CSS variables so the light theme can remap them
        (function () {
            var steps = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
            function palette(name) {
                var p = {};
                steps.forEach(function (s) { p[s] = 'rgb(var(--' + name + '-' + s + ') / <alpha-value>)'; });
                return p;
            }
            tailwind.config = {
                theme: { extend: { colors: { slate: palette('slate'), amber: palette('accent'), yellow: palette('accent') } } }
            };
        })();
    </script>
    
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ===== THEME TOKENS (RGB channels) ===== */
        /* Dark theme (default) = Tailwind's original slate / amber values */
        :root {
            color-scheme: dark;
            --slate-50: 248 250 252;  --slate-100: 241 245 249; --slate-200: 226 232 240;
            --slate-300: 203 213 225; --slate-400: 148 163 184; --slate-500: 100 116 139;
            --slate-600: 71 85 105;   --slate-700: 51 65 85;    --slate-800: 30 41 59;
            --slate-900: 15 23 42;    --slate-950: 2 6 23;

            --accent-50: 255 251 235;  --accent-100: 254 243 199; --accent-200: 253 230 138;
            --accent-300: 252 211 77;  --accent-400: 251 191 36;  --accent-500: 245 158 11;
            --accent-600: 217 119 6;   --accent-700: 180 83 9;    --accent-800: 146 64 14;
            --accent-900: 120 53 15;   --accent-950: 69 26 3;
        }

        /* Light theme: light backgrounds, dark text, blue accent */
        html.theme-light {
            color-scheme: light;
            --slate-50: 17 24 39;     --slate-100: 33 37 41;    --slate-200: 31 41 55;
            --slate-300: 55 65 81;    --slate-400: 85 89 96;    --slate-500: 107 114 128;
            --slate-600: 156 163 175; --slate-700: 209 213 219; --slate-800: 226 230 235;
            --slate-900: 255 255 255; --slate-950: 243 245 248;

            --accent-50: 239 246 255;  --accent-100: 219 234 254; --accent-200: 191 219 254;
            --accent-300: 144 202 249; --accent-400: 30 136 229;  --accent-500: 25 118 210;
            --accent-600: 21 101 192;  --accent-700: 13 71 161;   --accent-800: 10 60 140;
            --accent-900: 8 48 112;    --accent-950: 5 30 70;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom scrollbar style for premium feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgb(var(--slate-900));
        }

        ::-webkit-scrollbar-thumb {
            background: rgb(var(--accent-500));
            border-radius: 3px;
        }

        /* ===== LIGHT THEME DETAILS ===== */
        /* Blue top bar with white text */
        html.theme-light .app-header {
            background-color: rgb(25 118 210);
            border-color: rgb(25 118 210);
            backdrop-filter: none;
        }
        html.theme-light .app-header .header-pretitle { color: rgb(255 255 255 / 0.8); }
        html.theme-light .app-header .header-title { color: #fff; }
        html.theme-light .app-header .header-badge {
            background-color: rgb(255 255 255 / 0.15);
            border-color: rgb(255 255 255 / 0.35);
            color: #fff;
        }
        html.theme-light .app-header .header-badge-dot { background-color: #fff; }
        html.theme-light .app-header .theme-toggle {
            color: #fff;
            border-color: rgb(255 255 255 / 0.35);
            background-color: rgb(255 255 255 / 0.1);
        }
        html.theme-light .app-header .theme-toggle:hover { background-color: rgb(255 255 255 / 0.2); }

        /* Blue brand block at the top of the sidebar */
        html.theme-light .sidebar-brand {
            background-color: rgb(25 118 210);
            border-color: rgb(25 118 210);
        }
        html.theme-light .sidebar-brand .brand-logo { background-color: #fff; color: rgb(25 118 210); }
        html.theme-light .sidebar-brand .brand-name { color: #fff; }
        html.theme-light .sidebar-brand .brand-sub { color: rgb(255 255 255 / 0.8); }

        /* Soft shadow for white cards */
        html.theme-light main .bg-slate-900 {
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.06), 0 1px 2px rgb(0 0 0 / 0.04);
        }

        /* Theme toggle icon: moon in dark mode, sun in light mode */
        .theme-toggle .icon-sun { display: none; }
        html.theme-light .theme-toggle .icon-sun { display: inline-block; }
        html.theme-light .theme-toggle .icon-moon { display: none; }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col md:flex-row">

    <!-- SIDEBAR NAVIGATION -->
    @include('partials.sidebar')

    <!-- NOTIFICATION SYSTEM -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none"></div>

    <!-- MAIN CONTENT AREA -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- TOP HEADER -->
        @include('partials.header')

        <!-- VIEWPORTS CONTAINER -->
        <div class="p-8 flex-1">
            @yield('content')
        </div>
    </main>

    <!-- Global Toast Script -->
    <script>
        function toggleTheme() {
            const root = document.documentElement;
            const isLight = root.classList.toggle('theme-light');
            root.classList.toggle('theme-dark', !isLight);
            try { localStorage.setItem('nyds-theme', isLight ? 'light' : 'dark'); } catch (e) {}
        }

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 p-4 rounded-xl border text-sm shadow-xl transition-all duration-300 transform translate-x-12 opacity-0 pointer-events-auto max-w-sm `;

            if (type === 'success') {
                toast.className += 'bg-slate-900 border-green-500/50 text-green-400';
                toast.innerHTML = `<i class="fa-solid fa-circle-check text-lg"></i> <div>${message}</div>`;
            } else if (type === 'warning') {
                toast.className += 'bg-slate-900 border-amber-500/50 text-amber-400';
                toast.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-lg"></i> <div>${message}</div>`;
            } else {
                toast.className += 'bg-slate-900 border-slate-800 text-slate-100';
                toast.innerHTML = `<i class="fa-solid fa-info text-lg"></i> <div>${message}</div>`;
            }

            container.appendChild(toast);

            // Animate In
            setTimeout(() => {
                toast.classList.remove('translate-x-12', 'opacity-0');
            }, 50);

            // Animate Out & Remove
            setTimeout(() => {
                toast.classList.add('translate-x-12', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>

    @stack('scripts')
</body>

</html>
