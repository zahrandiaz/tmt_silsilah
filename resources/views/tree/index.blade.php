<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pohon Silsilah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="silsilah-container">
                        @if($ancestors->isEmpty())
                            <p>Belum ada data silsilah.</p>
                        @else
                            {{-- Mulai rekursi dari generasi teratas --}}
                            @include('partials.person-node', ['people' => $ancestors])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .silsilah-container ul {
            padding-left: 30px;
            list-style: none;
            position: relative;
        }
        .silsilah-container ul li {
            margin-top: 0.5rem;
            position: relative;
        }
        .silsilah-container ul li::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -20px;
            border-left: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            width: 20px;
            height: 25px;
            border-bottom-left-radius: 6px;
        }
        .silsilah-container > ul > li::before {
            border: none;
        }
        .person-block {
            padding: 8px;
            border-radius: 6px;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }
        .partner {
            margin-left: 8px;
            font-style: italic;
        }
    </style>
</x-app-layout>