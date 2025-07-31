<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cetak Silsilah ke PDF') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('pdf.generate') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="person_id" :value="__('Pilih Tokoh Awal Silsilah')" />
                                {{-- PERUBAHAN: Mengganti select dengan input --}}
                                <input type="text" name="person_id" id="person_id" placeholder="Ketik untuk mencari nama..." required>
                                <x-input-error :messages="$errors->get('person_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="generations" :value="__('Jumlah Generasi Keturunan yang Ditampilkan')" />
                                <x-text-input id="generations" class="block mt-1 w-full" type="number" name="generations" min="0" value="3" required />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Masukkan 0 untuk hanya menampilkan tokoh awal. Angka 1 akan menampilkan tokoh awal dan anaknya, dst.
                                </p>
                                <x-input-error :messages="$errors->get('generations')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <label for="with_photos" class="inline-flex items-center">
                                    <input id="with_photos" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="with_photos" value="1" checked>
                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Sertakan Foto Profil') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button>
                                {{ __('Buat PDF') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- TAMBAHKAN: Script untuk Tom Select --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new window.TomSelect('#person_id', {
                valueField: 'value',
                labelField: 'text',
                searchField: 'text',
                maxItems: 1,
                create: false,
                load: function(query, callback) {
                    if (!query.length) return callback();
                    let url = `{{ route('people.search') }}?search=${encodeURIComponent(query)}`;
                    fetch(url)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                },
                render: {
                    option: (item, escape) => `<div>${escape(item.text)}</div>`,
                    item: (item, escape) => `<div>${escape(item.text)}</div>`
                }
            });
        });
    </script>
    @endpush
</x-app-layout>