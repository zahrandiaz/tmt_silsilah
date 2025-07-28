<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Pengumuman Baru') }}
        </h2>
    </x-slot>
    
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('announcements.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="content" :value="__('Konten Pengumuman')" />
                                <input id="content" type="hidden" name="content" value="{{ old('content') }}">
                                <trix-editor input="content" class="mt-1 block w-full bg-white dark:bg-gray-900 dark:text-gray-300 trix-content"></trix-editor>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="type" :value="__('Tipe Warna')" />
                                <select name="type" id="type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="info" @selected(old('type') == 'info')>Biru (Info)</option>
                                    <option value="success" @selected(old('type') == 'success')>Hijau (Sukses)</option>
                                    <option value="warning" @selected(old('type') == 'warning')>Kuning (Peringatan)</option>
                                </select>
                            </div>

                            <div>
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1">
                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Aktifkan pengumuman ini?') }}</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">Jika dicentang, pengumuman lain yang sedang aktif akan otomatis dinonaktifkan.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('announcements.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Pengumuman') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>