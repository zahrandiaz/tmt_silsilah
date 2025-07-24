<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Anggota Keluarga Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- AWAL PERUBAHAN --}}
                    <div x-data="{ submitting: false }">
                        <form method="POST" action="{{ route('people.store') }}" @submit="submitting = true">
                    {{-- AKHIR PERUBAHAN --}}
                            @csrf

                            <!-- Nama -->
                            <div>
                                <x-input-label for="name" :value="__('Nama Lengkap')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Jenis Kelamin -->
                            <div class="mt-4">
                                <x-input-label for="gender" :value="__('Jenis Kelamin')" />
                                <select name="gender" id="gender" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="Laki-laki" @selected(old('gender') == 'Laki-laki')>Laki-laki</option>
                                    <option value="Perempuan" @selected(old('gender') == 'Perempuan')>Perempuan</option>
                                </select>
                            </div>

                            <!-- Ayah -->
                            <div class="mt-4">
                                <x-input-label for="father_id" :value="__('Ayah')" />
                                <select name="father_id" id="father_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Tidak Diketahui --</option>
                                    @foreach ($people->where('gender', 'Laki-laki') as $father)
                                        <option value="{{ $father->id }}" @selected(old('father_id') == $father->id)>{{ $father->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Ibu -->
                            <div class="mt-4">
                                <x-input-label for="mother_id" :value="__('Ibu')" />
                                <select name="mother_id" id="mother_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Tidak Diketahui --</option>
                                    @foreach ($people->where('gender', 'Perempuan') as $mother)
                                        <option value="{{ $mother->id }}" @selected(old('mother_id') == $mother->id)>{{ $mother->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tanggal Lahir & Tempat Lahir -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <x-input-label for="birth_date" :value="__('Tanggal Lahir')" />
                                    <x-text-input id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" :value="old('birth_date')" />
                                </div>
                                <div>
                                    <x-input-label for="birth_place" :value="__('Tempat Lahir')" />
                                    <x-text-input id="birth_place" class="block mt-1 w-full" type="text" name="birth_place" :value="old('birth_place')" />
                                </div>
                            </div>

                            <!-- Tanggal Wafat & Tempat Wafat -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <x-input-label for="death_date" :value="__('Tanggal Wafat')" />
                                    <x-text-input id="death_date" class="block mt-1 w-full" type="date" name="death_date" :value="old('death_date')" />
                                </div>
                                <div>
                                    <x-input-label for="death_place" :value="__('Tempat Wafat')" />
                                    <x-text-input id="death_place" class="block mt-1 w-full" type="text" name="death_place" :value="old('death_place')" />
                                </div>
                            </div>

                            <!-- Biografi -->
                            <div class="mt-4">
                                <x-input-label for="biography" :value="__('Biografi Singkat')" />
                                <textarea name="biography" id="biography" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('biography') }}</textarea>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <a href="{{ route('people.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                                {{-- AWAL PERUBAHAN --}}
                                <x-primary-button x-bind:disabled="submitting">
                                    <span x-show="!submitting">{{ __('Simpan') }}</span>
                                    <span x-show="submitting">{{ __('Menyimpan...') }}</span>
                                </x-primary-button>
                                {{-- AKHIR PERUBAHAN --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>