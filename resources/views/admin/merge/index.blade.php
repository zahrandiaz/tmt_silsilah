<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Alat Bantu: Gabungkan Data Duplikat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <p class="mb-4 text-gray-600 dark:text-gray-400">
                        Gunakan alat ini untuk menggabungkan dua data orang yang terduplikasi. Semua relasi dan foto dari "Data Duplikat" akan dipindahkan ke "Data Asli", kemudian "Data Duplikat" akan dihapus secara permanen.
                    </p>

                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('merge.process') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menggabungkan dua data ini? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="master_person_id" :value="__('Data Asli (Master - Yang Dipertahankan)')" />
                                <select id="master_person_id" name="master_person_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Pilih Data Asli</option>
                                    @foreach ($people as $person)
                                        <option value="{{ $person->id }}">{{ $person->name }} (ID: {{ $person->id }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('master_person_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="duplicate_person_id" :value="__('Data Duplikat (Yang Akan Dihapus)')" />
                                <select id="duplicate_person_id" name="duplicate_person_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Pilih Data Duplikat</option>
                                    @foreach ($people as $person)
                                        <option value="{{ $person->id }}">{{ $person->name }} (ID: {{ $person->id }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('duplicate_person_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button>
                                {{ __('Gabungkan Data') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>