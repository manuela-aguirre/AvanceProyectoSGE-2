<x-guest-layout>
    <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#15803d]">Rol activo</div>
    <h2 class="text-xl font-bold text-[#14532d]">Hola, {{ auth()->user()->name }}</h2>
    <p class="mt-1 text-sm text-slate-600">
        Su usuario tiene varios roles. Seleccione con cuál desea trabajar en esta sesión.
        Podrá cambiarlo en cualquier momento desde el menú de usuario.
    </p>

    <form method="POST" action="{{ route('active-role.update') }}" class="mt-4 space-y-3">
        @csrf
        @method('PUT')

        @foreach ($roles as $role)
            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-[#f0fdf4] has-[:checked]:border-[#14532d] has-[:checked]:bg-[#f0fdf4]">
                <input type="radio" name="role" value="{{ $role->name }}" class="text-[#14532d] focus:ring-[#15803d]"
                    @checked(old('role', $current) === $role->name)>
                <span class="flex-1">
                    <span class="block font-medium capitalize text-slate-900">{{ $role->name }}</span>
                    <span class="block text-xs text-slate-500">{{ $role->permissions_count }} permisos</span>
                </span>
            </label>
        @endforeach

        <x-input-error :messages="$errors->get('role')" />

        @if ($directCount > 0)
            <p class="text-xs text-slate-500">
                Además, tiene {{ $directCount }} permiso(s) directo(s) que aplican con cualquier rol.
            </p>
        @endif

        <div class="flex items-center justify-between pt-2">
            <button type="submit" class="inline-flex items-center rounded-md bg-[#14532d] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-[#166534] focus:outline-none focus:ring-2 focus:ring-[#15803d] focus:ring-offset-2">
                Continuar
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="text-sm text-slate-600 underline hover:text-[#14532d]">Cerrar sesión</button>
    </form>
</x-guest-layout>