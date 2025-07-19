<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Privasi</h3>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded-lg" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <label for="is_public" class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" id="is_public" name="is_public" class="sr-only" {{ $isPublic == '1' ? 'checked' : '' }}>
                                <div class="block bg-gray-600 w-14 h-8 rounded-full"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                            </div>
                            <div class="ml-3 text-gray-700 font-medium">
                                Silsilah Publik (Bisa diakses semua orang)
                            </div>
                        </label>

                        <div class="mt-6">
                            <x-primary-button>
                                {{ __('Simpan Pengaturan') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <hr class="my-6">

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ekspor Data</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Unduh semua data silsilah dalam format GEDCOM standar.
                    </p>
                    <a href="{{ route('export.gedcom') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Unduh File GEDCOM
                    </a>
                </div>
            </div>
        </div>
    </div>
    <style>
        input:checked ~ .dot {
            transform: translateX(100%);
            background-color: #48bb78;
        }
        input:checked ~ .block {
            background-color: #a7f3d0;
        }
    </style>
</x-app-layout>