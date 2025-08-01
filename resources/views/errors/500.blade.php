<x-guest-layout>
    <x-slot name="title">
        Kesalahan Server (500)
    </x-slot>

    <div class="px-4 py-16 text-center">
        <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">500</h1>
        <h2 class="mt-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Terjadi Kesalahan pada Server</h2>
        <p class="mt-2 text-base text-gray-500 dark:text-gray-400">Maaf, terjadi kesalahan tak terduga. Tim kami telah diberi tahu.</p>
        <div class="mt-6">
            <a href="{{ url('/') }}" class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Kembali ke Halaman Utama
            </a>
        </div>
    </div>
</x-guest-layout>