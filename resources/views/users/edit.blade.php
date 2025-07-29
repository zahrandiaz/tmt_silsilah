<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Ubah Pengguna:') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    {{-- Gunakan Alpine.js untuk mengelola state form --}}
                    <div x-data="{ role: '{{ old('role', $user->role) }}' }">
                        <form method="POST" action="{{ route('users.update', $user) }}">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-6">
                                {{-- Dropdown Peran --}}
                                <div>
                                    <x-input-label for="role" :value="__('Peran')" />
                                    <select name="role" id="role" x-model="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
                                        <option value="operator" @selected(old('role', $user->role) == 'operator')>Operator</option>
                                        <option value="user" @selected(old('role', $user->role) == 'user')>User</option>
                                    </select>
                                </div>

                                {{-- Form Pengaturan Operator (Hanya muncul jika peran adalah 'operator') --}}
                                <div x-show="role === 'operator'" x-transition class="p-4 border border-gray-200 dark:border-gray-700 rounded-md space-y-4">
                                    <h3 class="font-semibold text-lg">Pengaturan Hak Akses Operator</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Atur garis keturunan dan jangkauan generasi yang dapat dikelola oleh operator ini.</p>
                                    
                                    {{-- Pilih Akar Silsilah --}}
                                    <div>
                                        <x-input-label for="operator_person_id" :value="__('Akar Garis Keturunan')" />
                                        <select name="operator_person_id" id="operator_person_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                            <option value="">-- Pilih Seseorang --</option>
                                            @foreach ($people as $person)
                                                <option value="{{ $person->id }}" @selected(old('operator_person_id', $user->accessControl?->person_id) == $person->id)>
                                                    {{ $person->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('operator_person_id')" class="mt-2" />
                                    </div>

                                    {{-- Batas Generasi --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="generations_up" :value="__('Jangkauan ke Atas (Leluhur)')" />
                                            <x-text-input id="generations_up" class="block mt-1 w-full" type="number" name="generations_up" min="0" value="{{ old('generations_up', $user->accessControl?->generations_up ?? 0) }}" />
                                            <x-input-error :messages="$errors->get('generations_up')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="generations_down" :value="__('Jangkauan ke Bawah (Keturunan)')" />
                                            <x-text-input id="generations_down" class="block mt-1 w-full" type="number" name="generations_down" min="0" value="{{ old('generations_down', $user->accessControl?->generations_down ?? 0) }}" />
                                            <x-input-error :messages="$errors->get('generations_down')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Form Tautan Person (Hanya muncul jika peran adalah 'user') --}}
                                <div x-show="role === 'user'" x-transition>
                                    <x-input-label for="person_id" :value="__('Tautkan ke Anggota Silsilah (untuk Peran User)')" />
                                    <select name="person_id" id="person_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">-- Tidak Ditautkan --</option>
                                        @foreach ($people as $person)
                                            @php
                                                $isLinkedToOtherUser = $person->user && $person->user->id !== $user->id;
                                            @endphp
                                            <option value="{{ $person->id }}" @selected(old('person_id', $user->person_id) == $person->id) @disabled($isLinkedToOtherUser)>
                                                {{ $person->name }}
                                                @if ($isLinkedToOtherUser)
                                                    (Ditautkan ke: {{ $person->user->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
                                    Batal
                                </a>
                                <x-primary-button>
                                    {{ __('Simpan Perubahan') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>