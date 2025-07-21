<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Silsilah: {{ $person->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">{{ $person->name }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Lahir: {{ $person->birth_date ?? '?' }} di {{ $person->birth_place ?? '?' }}
                    @if($person->death_date)
                        <br>Wafat: {{ $person->death_date }} di {{ $person->death_place ?? '?' }}
                    @endif
                </p>
                @if($person->biography)
                    <p class="mt-4">{{ $person->biography }}</p>
                @endif
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Leluhur</h3>
                <div class="silsilah-container">
                    @include('partials.ancestor-node', ['person' => $person])
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pasangan & Keturunan</h3>
                <div class="silsilah-container">
                    @include('partials.descendant-node', ['person' => $person, 'level' => 0])
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Style ini sudah benar, biarkan saja */
        .silsilah-container ul { padding-left: 20px; list-style: none; position: relative; }
        .silsilah-container ul li { margin-top: 10px; position: relative; }
        .silsilah-container ul li::before { content: ''; position: absolute; top: 0; left: -20px; border-left: 1px solid #ccc; border-bottom: 1px solid #ccc; width: 20px; height: 1em; }
        .silsilah-container > ul > li::before { border: none; }
        .person-block { display: inline-block; }
        .partner { font-style: italic; color: #4b5563; }
    </style>
</x-app-layout>