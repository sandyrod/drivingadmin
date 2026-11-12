<aside class="w-full md:w-64 bg-slate-900 border-r border-slate-800 flex flex-col shrink-0">
    <!-- Brand / Logo Section -->
    <div class="sidebar-brand p-6 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="brand-logo bg-amber-500 text-slate-950 p-2.5 rounded-lg font-black tracking-tighter text-xl flex items-center justify-center">
                NYDS
            </div>
            <div>
                <h1 class="brand-name font-extrabold text-sm uppercase tracking-wider text-amber-500">NY Driving</h1>
                <p class="brand-sub text-xs text-slate-400">School & Academy</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 p-4 space-y-1">
        @php
            $navItems = [
                [
                    'id' => 'dashboard',
                    'route' => 'dashboard',
                    'path' => 'dashboard',
                    'icon' => 'fa-solid fa-chart-line',
                    'label' => 'Dashboard Principal'
                ],
                [
                    'id' => 'inscripcion',
                    'route' => 'inscripcion',
                    'path' => 'inscripcion',
                    'icon' => 'fa-solid fa-user-plus',
                    'label' => 'Inscripción Alumno'
                ],
                [
                    'id' => 'abonos',
                    'route' => 'abonos',
                    'path' => 'abonos',
                    'icon' => 'fa-solid fa-file-invoice-dollar',
                    'label' => 'Registro de Abonos'
                ],
                [
                    'id' => 'agenda',
                    'route' => 'agenda',
                    'path' => 'agenda',
                    'icon' => 'fa-solid fa-calendar-days',
                    'label' => 'Agenda de Clases'
                ],
                [
                    'id' => 'paquetes',
                    'route' => 'paquetes',
                    'path' => 'paquetes',
                    'icon' => 'fa-solid fa-boxes-stacked',
                    'label' => 'Paquetes'
                ],
                [
                    'id' => 'registro-instructor',
                    'route' => 'registro-instructor',
                    'path' => 'registro-instructor',
                    'icon' => 'fa-solid fa-user-tie',
                    'label' => 'Registrar Instructor'
                ],
                [
                    'id' => 'registro-vehiculo',
                    'route' => 'registro-vehiculo',
                    'path' => 'registro-vehiculo',
                    'icon' => 'fa-solid fa-car',
                    'label' => 'Registrar Vehículo'
                ],
                [
                    'id' => 'encuesta',
                    'route' => 'encuesta',
                    'path' => 'encuesta',
                    'icon' => 'fa-solid fa-star-half-stroke',
                    'label' => 'Encuesta de Servicio'
                ],
                [
                    'id' => 'sheets-db',
                    'route' => 'sheets-db',
                    'path' => 'sheets-db',
                    'icon' => 'fa-solid fa-table-cells',
                    'label' => 'Simulador de Google Sheets'
                ],
                [
                    'id' => 'apps-script',
                    'route' => 'apps-script',
                    'path' => 'apps-script',
                    'icon' => 'fa-solid fa-gears',
                    'label' => 'Automatizaciones Script'
                ],
            ];
        @endphp

        @foreach($navItems as $item)
            @php
                // Generate URL based on named route availability, fallback to relative path
                $url = Route::has($item['route']) ? route($item['route']) : url('/' . $item['path']);
                
                // Determine if active (checks path, subpaths, or route names)
                $isActive = request()->is($item['path']) 
                    || request()->is($item['path'] . '/*') 
                    || request()->routeIs($item['route']);
                
                // Active/Inactive class lists matching premium styling of driving.blade
                $activeClass = 'bg-slate-800 text-amber-500 border-l-4 border-amber-500';
                $inactiveClass = 'text-slate-400 hover:bg-slate-800 hover:text-slate-100';
                $currentClass = $isActive ? $activeClass : $inactiveClass;
            @endphp
            
            @if(isset($isTabbed) && $isTabbed)
                <!-- SPA Tab switching behavior (Retrocompatibility) -->
                <button onclick="switchTab('{{ $item['id'] }}')" id="btn-{{ $item['id'] }}"
                    class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ $item['id'] === 'dashboard' ? $activeClass : $inactiveClass }}">
                    <i class="{{ $item['icon'] }} text-lg"></i>
                    <span>{{ $item['label'] }}</span>
                </button>
            @else
                <!-- Classic Laravel Route navigation -->
                <a href="{{ $url }}" id="btn-{{ $item['id'] }}"
                    class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition {{ $currentClass }}">
                    <i class="{{ $item['icon'] }} text-lg"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <!-- Logged-in user + logout -->
    @auth
        <div class="px-4 pt-4 border-t border-slate-800">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-100 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-red-500 transition">
                    <i class="fa-solid fa-right-from-bracket text-lg"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    @endauth

    <!-- Footer Information -->
    <div class="p-4 border-t border-slate-800 text-xs text-slate-500">
        <p>Demo Técnico - Fase 1</p>
        <p class="text-amber-500/60 mt-1">Ecosistema Google Activo</p>
    </div>
</aside>
