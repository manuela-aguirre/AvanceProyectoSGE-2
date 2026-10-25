@props(['active' => true])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium '.($active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')]) }}>
    {{ $active ? 'Activo' : 'Inactivo' }}
</span>
