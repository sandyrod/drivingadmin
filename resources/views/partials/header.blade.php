<header class="bg-slate-900/50 backdrop-blur-md border-b border-slate-800 px-8 py-4 flex items-center justify-between">
    <div>
        <!-- Dynamic Pre-title section -->
        <span class="text-xs font-semibold uppercase tracking-widest text-slate-400" id="section-pre-title">
            @yield('section-pre-title', $sectionPreTitle ?? 'Gestión de operaciones')
        </span>

        <!-- Dynamic Title section -->
        <h2 class="text-xl font-bold text-slate-100" id="section-title">
            @yield('section-title', $sectionTitle ?? 'Dashboard Principal')
        </h2>
    </div>

    <!-- Status / Info Badge -->
    <div class="flex items-center gap-3">
        <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-500 border border-amber-500/20 animate-pulse">
            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 mr-1.5"></span>
            Activo
        </span>
    </div>
</header>