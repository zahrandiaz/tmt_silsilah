<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Silsilah Keluarga Besar') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900">
                    
                    <div class="text-center mb-12">
                        <h1 class="text-3xl font-bold text-gray-800">Selamat Datang</h1>
                        <p class="mt-2 text-gray-600">Jelajahi silsilah melalui tokoh-tokoh kunci di bawah ini.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12 border-t border-b py-8">
                        <div class="text-center">
                            <p class="text-4xl font-bold text-blue-600">{{ $totalPeople }}</p>
                            <p class="text-sm text-gray-500 uppercase tracking-wider">Total Individu Tercatat</p>
                        </div>
                        <div class="text-center">
                            <p class="text-4xl font-bold text-green-600">{{ $totalGenerations }}</p>
                            <p class="text-sm text-gray-500 uppercase tracking-wider">Jumlah Generasi</p>
                        </div>
                    </div>
                    @if($keyFigures->isNotEmpty())
                        <div class="text-center mb-8">
                            <h3 class="text-xl font-semibold text-gray-700">Pintu Gerbang Silsilah</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                            @foreach($keyFigures as $figure)
                                <a href="{{ route('people.show', $figure) }}" class="group block text-center transition-transform duration-300 transform hover:scale-105">
                                    <div class="relative w-40 h-40 mx-auto">
                                        @if($figure->photos->isNotEmpty())
                                            <img src="{{ asset('storage/' . $figure->photos->first()->image_path) }}" alt="{{ $figure->name }}" class="w-40 h-40 rounded-full object-cover shadow-lg mx-auto">
                                        @else
                                            <div class="w-40 h-40 rounded-full bg-gray-200 flex items-center justify-center shadow-lg mx-auto">
                                                <span class="text-4xl font-bold text-gray-500">
                                                    @php
                                                        $words = explode(' ', $figure->name);
                                                        $initials = '';
                                                        if (isset($words[0])) $initials .= strtoupper(substr($words[0], 0, 1));
                                                        if (isset($words[1])) $initials .= strtoupper(substr($words[1], 0, 1));
                                                    @endphp
                                                    {{ $initials ?: '?' }}
                                                </span>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 rounded-full border-4 border-white group-hover:border-blue-400 transition-colors duration-300"></div>
                                    </div>
                                    <div class="mt-4">
                                        <h2 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors duration-300">{{ $figure->name }}</h2>
                                        <p class="mt-1 text-sm/relaxed text-gray-500">
                                            Lahir: {{ $figure->birth_date_formatted }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16">
                            <p class="text-gray-500">Belum ada tokoh kunci yang ditandai untuk ditampilkan.</p>
                            @auth
                                @if(auth()->user()->role === 'admin')
                                    <p class="mt-2 text-sm text-gray-500">Anda dapat menandai seseorang sebagai tokoh kunci melalui halaman <a href="{{ route('people.index') }}" class="text-blue-600 hover:underline">manajemen anggota</a>.</p>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>