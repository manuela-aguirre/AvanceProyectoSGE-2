<x-app-layout>
 <x-slot name="header">
 <h2 class="font-semibold text-xl text-gray-800 leading-tight">
 Dashboard — Bienvenido, {{ auth()->user()->name }}
 </h2>
 </x-slot>
 <div class="py-6">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
 {{-- Roles --}}
 <div class="bg-white shadow rounded p-4">
 <p class="text-sm text-gray-600">
 <strong>Roles asignados:</strong>
 {{ $roles->isEmpty() ? 'Sin rol asignado' : $roles->join(', ') }}
 </p>
 </div>
 {{-- KPIs --}}
 @if ($stats->isNotEmpty())
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
 @foreach ($stats as $stat)
 <div class="bg-white shadow rounded p-4 border-l-4 border-blue-500">
 <p class="text-sm text-gray-500">{{ $stat['label'] }}</p>
 <p class="text-xl font-bold text-gray-800 mt-1">{{ $stat['value'] }}</p>
 </div>
 @endforeach
 </div>
 @endif
 {{-- Top productos --}}
 @if ($topProducts->isNotEmpty())
 <div class="bg-white shadow rounded p-6">
 <h3 class="font-semibold text-lg mb-4">Top 5 productos más vendidos este mes</h3>
 <table class="min-w-full text-sm">
 <thead>
 <tr class="border-b text-left">
 <th class="p-2">#</th>
 <th class="p-2">Producto</th>
 <th class="p-2 text-right">Unidades vendidas</th>
 </tr>
 </thead>
 <tbody>
 @foreach ($topProducts as $i => $item)
 <tr class="border-b">
 <td class="p-2">{{ $i + 1 }}</td>
 <td class="p-2">{{ $item->product?->nombre }}</td>
 <td class="p-2 text-right">{{ $item->total_sold }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @endif
 {{-- Módulos --}}
 @if ($modules->isNotEmpty())
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
 @foreach ($modules as $module)
 <a href="{{ route($module['route']) }}"
 class="bg-white shadow rounded p-6 hover:shadow-lg transition">
 <h3 class="font-semibold text-lg text-blue-700">{{ $module['title'] }}</h3>
 <p class="text-sm text-gray-600 mt-2">{{ $module['description'] }}</p>
 </a>
 @endforeach
 </div>
 @endif
 </div>
 </div>
</x-app-layout>