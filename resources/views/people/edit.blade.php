<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ubah Data Anggota Keluarga') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div x-data="{ submitting: false }">
                        <form method="POST" action="{{ route('people.update', $person) }}" @submit="submitting = true">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-input-label for="name" :value="__('Nama Lengkap')" />
                                <x-text-input id="name" class="block mt-1 w-full capitalize-input" type="text" name="name" :value="old('name', $person->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="gender" :value="__('Jenis Kelamin')" />
                                <select name="gender" id="gender" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="Laki-laki" @selected(old('gender', $person->gender) == 'Laki-laki')>Laki-laki</option>
                                    <option value="Perempuan" @selected(old('gender', $person->gender) == 'Perempuan')>Perempuan</option>
                                </select>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="father_id" :value="__('Ayah')" />
                                <input type="text" id="father_id" name="father_id" placeholder="Ketik untuk mencari nama ayah...">
                                <x-input-error :messages="$errors->get('father_id')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="mother_id" :value="__('Ibu')" />
                                <input type="text" id="mother_id" name="mother_id" placeholder="Ketik untuk mencari nama ibu...">
                                <x-input-error :messages="$errors->get('mother_id')" class="mt-2" />
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <x-input-label for="birth_date" :value="__('Tanggal Lahir')" />
                                    <x-text-input id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" :value="old('birth_date', $person->birth_date)" />
                                </div>
                                <div>
                                    <x-input-label for="birth_place" :value="__('Tempat Lahir')" />
                                    <x-text-input id="birth_place" class="block mt-1 w-full" type="text" name="birth_place" :value="old('birth_place', $person->birth_place)" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <x-input-label for="death_date" :value="__('Tanggal Wafat')" />
                                    <x-text-input id="death_date" class="block mt-1 w-full" type="date" name="death_date" :value="old('death_date', $person->death_date)" />
                                </div>
                                <div>
                                    <x-input-label for="death_place" :value="__('Tempat Wafat')" />
                                    <x-text-input id="death_place" class="block mt-1 w-full" type="text" name="death_place" :value="old('death_place', $person->death_place)" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="biography" :value="__('Biografi Singkat')" />
                                <textarea name="biography" id="biography" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('biography', $person->biography) }}</textarea>
                            </div>

                            <div class="mt-4">
                                <label for="is_key_figure" class="inline-flex items-center">
                                    <input id="is_key_figure" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_key_figure" value="1"
                                        @if(old('is_key_figure', $person->is_key_figure)) checked @endif
                                    >
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Tandai sebagai Tokoh Kunci (tampil di halaman depan)') }}</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <a href="{{ route('people.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                                <x-primary-button x-bind:disabled="submitting">
                                    <span x-show="!submitting">{{ __('Simpan') }}</span>
                                    <span x-show="submitting">{{ __('Menyimpan...') }}</span>
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fungsi untuk membuat instance Tom Select dengan konfigurasi
            function createTomSelect(selector, gender, initialOptions = []) {
                return new window.TomSelect(selector, {
                    valueField: 'value',
                    labelField: 'text',
                    searchField: 'text',
                    maxItems: 1,
                    options: initialOptions, // <-- Menambahkan data awal
                    create: false,
                    load: function(query, callback) {
                        if (!query.length) return callback();
                        let url = `{{ route('people.search') }}?search=${encodeURIComponent(query)}`;
                        if (gender) {
                            url += `&gender=${gender}`;
                        }
                        
                        fetch(url)
                            .then(response => response.json())
                            .then(json => {
                                callback(json);
                            }).catch(()=>{
                                callback();
                            });
                    },
                    render: {
                        option: function(item, escape) {
                            return `<div>${escape(item.text)}</div>`;
                        },
                        item: function(item, escape) {
                            return `<div>${escape(item.text)}</div>`;
                        }
                    }
                });
            }

            // Menyiapkan data awal untuk Ayah
            let initialFatherOptions = [];
            @if($father)
                initialFatherOptions.push({
                    value: '{{ $father->id }}',
                    text: '{{ $father->name }} (ID: {{ $father->id }})'
                });
            @endif
            const fatherSelect = createTomSelect('#father_id', 'Laki-laki', initialFatherOptions);
            @if($father)
                fatherSelect.setValue('{{ $father->id }}');
            @endif


            // Menyiapkan data awal untuk Ibu
            let initialMotherOptions = [];
            @if($mother)
                initialMotherOptions.push({
                    value: '{{ $mother->id }}',
                    text: '{{ $mother->name }} (ID: {{ $mother->id }})'
                });
            @endif
            const motherSelect = createTomSelect('#mother_id', 'Perempuan', initialMotherOptions);
            @if($mother)
                motherSelect.setValue('{{ $mother->id }}');
            @endif
        });
    </script>
    @endpush
</x-app-layout>