<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Silsilah: {{ $person->name }}
        </h2>
    </x-slot>

    <div x-data="{ showModal: false, largeImageUrl: '' }" @keydown.escape.window="showModal = false">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                        <li class="inline-flex items-center">
                            <a href="{{ route('people.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                                </svg>
                                Silsilah
                            </a>
                        </li>
                        @foreach ($breadcrumbs as $ancestor)
                        <li>
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <a href="{{ route('people.show', $ancestor) }}" class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2">{{ $ancestor->name }}</a>
                            </div>
                        </li>
                        @endforeach
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">{{ $person->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900">{{ $person->name }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{-- AWAL PERUBAHAN --}}
                        Lahir: {{ $person->birth_date_formatted }} di {{ $person->birth_place ?? '?' }}
                        @if($person->death_date_formatted)
                            <br>Wafat: {{ $person->death_date_formatted }} di {{ $person->death_place ?? '?' }}
                        @endif
                        {{-- AKHIR PERUBAHAN --}}
                    </p>
                    @if($person->biography)
                        <p class="mt-4">{{ $person->biography }}</p>
                    @endif
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Galeri Foto</h3>
                    @can('update', $person)
                        <div class="mb-6 p-4 border rounded-md">
                            <form action="{{ route('photos.store', $person) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div>
                                    <x-input-label for="photo" :value="__('Unggah Foto Baru (Max: 5MB)')" />
                                    <x-text-input id="photo" class="block mt-1 w-full" type="file" name="photo" required />
                                    <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="category" :value="__('Kategori')" />
                                    <select name="category" id="category" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="Masa Kecil">Masa Kecil</option>
                                        <option value="Masa Dewasa">Masa Dewasa</option>
                                        <option value="Masa Tua">Masa Tua</option>
                                        <option value="Galeri" selected>Galeri (Lainnya)</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('category')" class="mt-2" />
                                </div>
                                <div class="mt-4">
                                    <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
                                    <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" :value="old('description')" />
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>
                                <div class="mt-4">
                                    <x-primary-button>{{ __('Unggah') }}</x-primary-button>
                                </div>
                            </form>
                        </div>
                    @endcan
                    @if (session('success'))
                        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
                    @endif
                    @php
                        $photosByCategory = $person->photos->groupBy('category');
                        $categories = ['Masa Kecil', 'Masa Dewasa', 'Masa Tua', 'Galeri'];
                    @endphp
                    @if ($person->photos->isNotEmpty())
                        <div class="space-y-6">
                            @foreach ($categories as $category)
                                @if(isset($photosByCategory[$category]) && $photosByCategory[$category]->isNotEmpty())
                                    <div>
                                        <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">{{ $category }}</h4>
                                        <ul class="space-y-3">
                                            @foreach ($photosByCategory[$category] as $photo)
                                                <li class="flex items-center space-x-4 p-2 rounded-md hover:bg-gray-50">
                                                    <img 
                                                        src="{{ asset('storage/' . $photo->image_path) }}" 
                                                        alt="{{ $photo->description ?? 'Foto ' . $person->name }}" 
                                                        class="w-20 h-20 object-cover rounded-md cursor-pointer flex-shrink-0"
                                                        @click="largeImageUrl = '{{ asset('storage/' . $photo->image_path) }}'; showModal = true">
                                                    <div class="flex-grow">
                                                        <p class="text-sm text-gray-700">{{ $photo->description }}</p>
                                                    </div>
                                                    @can('delete', $person)
                                                        <div class="flex-shrink-0">
                                                            <form action="{{ route('photos.destroy', $photo) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <x-danger-button type="submit" class="text-xs !py-1 !px-2">Hapus</x-danger-button>
                                                            </form>
                                                        </div>
                                                    @endcan
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada foto untuk anggota ini.</p>
                    @endif
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Leluhur</h3>
                    <div class="silsilah-container">
                        @if($person->father() || $person->mother())
                            @include('partials.ancestor-node', ['person' => $person])
                        @else
                            <p class="text-sm text-gray-500">Belum ada data leluhur (orang tua) yang ditambahkan.</p>
                        @endif
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pasangan & Keturunan</h3>
                    <div class="silsilah-container">
                        @if($person->spouses()->isNotEmpty() || $person->allChildren()->isNotEmpty())
                            @include('partials.descendant-node', ['person' => $person, 'level' => 0])
                        @else
                            <p class="text-sm text-gray-500">Belum ada data pasangan atau keturunan yang ditambahkan.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div 
            x-show="showModal" 
            x-cloak 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        >
            <div class="fixed inset-0 bg-black bg-opacity-75" @click="showModal = false"></div>

            <div 
                x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col"
            >
                <button @click="showModal = false" class="absolute -top-3 -right-3 z-10 bg-white rounded-full p-1">
                    <svg class="h-6 w-6 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="p-4 flex-grow flex items-center justify-center">
                    <img :src="largeImageUrl" alt="Tampilan Penuh" class="max-w-full max-h-full object-contain">
                </div>
            </div>
        </div>
    </div>

    <style>
        .silsilah-container ul { padding-left: 20px; list-style: none; position: relative; }
        .silsilah-container ul li { margin-top: 10px; position: relative; }
        .silsilah-container ul li::before { content: ''; position: absolute; top: 0; left: -20px; border-left: 1px solid #ccc; border-bottom: 1px solid #ccc; width: 20px; height: 1em; }
        .silsilah-container > ul > li::before { border: none; }
        .person-block { display: inline-block; }
        .partner { font-style: italic; color: #4b5563; }
        [x-cloak] { display: none !important; }
    </style>
</x-app-layout>