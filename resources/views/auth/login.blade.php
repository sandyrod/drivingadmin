<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Iniciar sesión - NY Driving School</title>

    @include('partials.theme-head')
</head>

<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col">

    <!-- Top bar (blue in the light theme, like the app header) -->
    <header class="app-header sidebar-brand bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="brand-logo bg-amber-500 text-slate-950 px-2 py-1.5 rounded-lg font-black tracking-tighter text-base">
                NYDS
            </div>
            <div>
                <h1 class="brand-name font-extrabold text-sm uppercase tracking-wider text-amber-500">NY Driving</h1>
                <p class="brand-sub text-xs text-slate-400">School & Academy</p>
            </div>
        </div>

        <button type="button" onclick="toggleTheme()" title="Cambiar tema claro / oscuro"
            class="theme-toggle w-9 h-9 rounded-lg border border-slate-800 bg-slate-800/40 text-slate-400 hover:text-amber-500 hover:border-amber-500/60 flex items-center justify-center transition">
            <i class="icon-moon fa-solid fa-moon"></i>
            <i class="icon-sun fa-solid fa-sun"></i>
        </button>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-xl">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl mb-5">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-100">Iniciar sesión</h2>
                <p class="text-sm text-slate-400 mt-1 mb-6">Ingresa con tu cuenta para acceder al panel.</p>

                @if ($errors->any())
                    <div class="mb-5 flex items-start gap-2 rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2.5 text-sm text-red-500" role="alert">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Correo</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-9 pr-3 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 transition"
                                placeholder="usuario@correo.com">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Contraseña</label>
                        <div class="relative">
                            <i class="fa-solid fa-key absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-9 pr-10 py-2.5 text-sm text-slate-100 placeholder-slate-600 focus:outline-none focus:border-amber-500 transition"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword()" title="Mostrar / ocultar contraseña"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 text-slate-500 hover:text-amber-500 transition">
                                <i id="password-eye" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-400 select-none cursor-pointer">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-slate-700 accent-amber-500">
                        Mantener sesión iniciada
                    </label>

                    <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 rounded-lg text-sm transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Entrar
                    </button>
                </form>
            </div>

            <p class="text-center text-xs text-slate-500 mt-6">&copy; {{ date('Y') }} NY Driving School & Academy</p>
        </div>
    </main>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eye = document.getElementById('password-eye');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eye.className = `fa-solid ${show ? 'fa-eye-slash' : 'fa-eye'} text-sm`;
        }
    </script>
</body>

</html>
