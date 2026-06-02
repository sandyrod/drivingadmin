<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NY Driving School - Dashboard')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Custom scrollbar style for premium feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #eab308;
            border-radius: 3px;
        }
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
