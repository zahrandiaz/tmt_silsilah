<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Anggota Keluarga') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded-lg" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <!-- Tombol Tambah Data -->
                    <a href="{{ route('people.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-4">
                        Tambah Anggota
                    </a>

                    <!-- Tabel Data -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y-2 divide-gray-200 bg-white text-sm">
                            <thead class="text-left">
                                <tr>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Nama</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Jenis Kelamin</th>
                                    <th class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">Tanggal Lahir</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @forelse ($people as $person)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-2 font-medium text-gray-900">{{ $person->name }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $person->gender }}</td>
                                    <td class="whitespace-nowrap px-4 py-2 text-gray-700">{{ $person->birth_date ? \Carbon\Carbon::parse($person->birth_date)->format('d F Y') : '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-2">
                                        <a href="#" class="inline-block rounded bg-yellow-500 px-4 py-2 text-xs font-medium text-black hover:bg-yellow-600">
                                            Lihat
                                        </a>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-2">
                                        <div class="flex items-center space-x-2">
                                            @can('update', $person)
                                                <a href="{{ route('people.edit', $person) }}" class="inline-block rounded bg-yellow-500 px-4 py-2 text-xs font-medium text-black hover:bg-yellow-600">
                                                    Edit
                                                </a>
                                            @endcan
                                            @can('delete', $person)
                                                <form action="{{ route('people.destroy', $person) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-block rounded bg-red-600 px-4 py-2 text-xs font-medium text-white hover:bg-red-700">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-gray-500 py-4">
                                        Belum ada data. Silakan tambah anggota baru.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginasi -->
                    <div class="mt-4">
                        {{ $people->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>