@auth
    {{-- TAMPILAN UNTUK PENGGUNA YANG SUDAH LOGIN --}}
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Silsilah: {{ $person->name }}
            </h2>
        </x-slot>

        {{-- Memanggil konten detail dari file terpisah --}}
        @include('partials.person-detail-content', ['person' => $person, 'breadcrumbs' => $breadcrumbs])
    </x-app-layout>
@else
    {{-- TAMPILAN UNTUK PENGUNJUNG PUBLIK (TAMU) --}}
    <x-guest-layout>
        {{-- Memanggil konten detail yang sama dari file terpisah --}}
        @include('partials.person-detail-content', ['person' => $person, 'breadcrumbs' => $breadcrumbs])
    </x-guest-layout>
@endauth