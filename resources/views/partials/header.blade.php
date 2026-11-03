<header class="app-header bg-slate-900/50 backdrop-blur-md border-b border-slate-800 px-8 py-4 flex items-center justify-between">
    <div>
        <!-- Dynamic Pre-title section -->
        <span class="header-pretitle text-xs font-semibold uppercase tracking-widest text-slate-400" id="section-pre-title">
            @yield('section-pre-title', $sectionPreTitle ?? 'Gestión de operaciones')
        </span>

        <!-- Dynamic Title section -->
        <h2 class="header-title text-xl font-bold text-slate-100" id="section-title">
            @yield('section-title', $sectionTitle ?? 'Dashboard Principal')
        </h2>
    </div>

    <!-- Theme toggle + Status / Info Badge -->
    <div class="flex items-center gap-3">
        <button type="button" onclick="toggleTheme()" title="Cambiar tema claro / oscuro"
            class="theme-toggle w-9 h-9 rounded-lg border border-slate-800 bg-slate-800/40 text-slate-400 hover:text-amber-500 hover:border-amber-500/60 flex items-center justify-center transition">
            <i class="icon-moon fa-solid fa-moon"></i>
            <i class="icon-sun fa-solid fa-sun"></i>
        </button>

        <span
            class="header-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-500 border border-amber-500/20 animate-pulse">
            <span class="header-badge-dot h-1.5 w-1.5 rounded-full bg-amber-500 mr-1.5"></span>
            Activo
        </span>
    </div>
</header>
