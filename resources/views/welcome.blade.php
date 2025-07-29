{{-- Use the new guest layout --}}
<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- [TAMBAHKAN BLOK INI] --}}
            @if(isset($announcement))
            <div class="mb-6 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
                <p class="font-bold">Pengumuman</p>
                <div class="prose dark:prose-invert max-w-none">{!! $announcement->content !!}</div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Selamat Datang di Silsilah Keluarga</h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Jelajahi silsilah keluarga besar kita.</p>
                    </div>

                    <div class="mb-12 max-w-2xl mx-auto">
                        <form action="{{ route('silsilah.index') }}" method="GET">
                            <div class="flex rounded-md shadow-sm">
                                <input type="search" name="search" id="search" value="{{ $searchQuery ?? '' }}" class="block w-full flex-1 rounded-none rounded-s-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 sm:text-sm" placeholder="Cari nama anggota keluarga...">
                                <button type="submit" class="inline-flex items-center rounded-e-lg border border-s-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 focus:z-10 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    Cari
                                </button>
                                @if($searchQuery)
                                    <a href="{{ route('silsilah.index') }}" class="ml-2 inline-flex items-center rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Reset</a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12 border-t border-b py-8">
                        {{-- Statistics section remains the same --}}
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
                             {{-- Dynamic title based on search --}}
                            <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300">{{ $searchQuery ? 'Hasil Pencarian' : 'Pintu Gerbang Silsilah' }}</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                            {{-- Key Figures / Search Results loop remains the same --}}
                            @foreach($keyFigures as $figure)
                                <a href="{{ route('people.show', $figure) }}" class="group block text-center transition-transform duration-300 transform hover:scale-105">
                                    <div class="relative w-40 h-40 mx-auto">
                                        @if($figure->photos->isNotEmpty())
                                            @php
                                                $profilePicture = $figure->profilePicture();
                                            @endphp
                                            @if($profilePicture)
                                                <img src="{{ asset('storage/' . $profilePicture->image_path) }}" alt="{{ $figure->name }}" class="w-40 h-40 rounded-full object-cover shadow-lg mx-auto">
                                            @else
                                                {{-- Tampilan placeholder jika tidak ada foto sama sekali --}}
                                                <div class="w-40 h-40 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center shadow-lg mx-auto">
                                                    <span class="text-4xl font-bold text-gray-500 dark:text-gray-400">
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
                                        @else
                                            <div class="w-40 h-40 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center shadow-lg mx-auto">
                                                <span class="text-4xl font-bold text-gray-500 dark:text-gray-400">
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
                                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 group-hover:text-blue-600 transition-colors duration-300">{{ $figure->name }}</h2>
                                        <p class="mt-1 text-sm/relaxed text-gray-500 dark:text-gray-400">
                                            Lahir: {{ $figure->birth_date_formatted }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16">
                            <p class="text-gray-500 dark:text-gray-400">{{ $searchQuery ? 'Tidak ada hasil yang cocok dengan pencarian Anda.' : 'Belum ada tokoh kunci yang ditandai.' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>