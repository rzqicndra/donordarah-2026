<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ $isSuperAdmin ? 'Peran: Super Admin' : 'Peran: Petugas (Staff)' }}</x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-300">
            @if ($isSuperAdmin)
                Anda mengelola akun pengguna, role, hak akses, konfigurasi, serta melakukan pengawasan
                terhadap seluruh proses donor darah. Hindari menghapus data operasional yang masih
                diperlukan untuk audit dan pelaporan.
            @else
                Anda menjalankan kegiatan donor harian: pengelolaan lokasi, jadwal, pendaftaran,
                pencatatan pemeriksaan, stok, permintaan, dan distribusi darah.
                Pengelolaan akun, role, dan penghapusan data berada di bawah Super Admin.
            @endif
        </p>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Pembagian pekerjaan operasional</x-slot>
        <x-slot name="description">Daftar kewenangan dasar untuk akun Petugas.</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-200">
                    <tr>
                        <th class="px-3 py-3 font-semibold">Bidang pekerjaan</th>
                        <th class="px-3 py-3 font-semibold">Tanggung jawab</th>
                        <th class="px-3 py-3 font-semibold">Hak akses Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($staffDuties as $duty)
                        <tr>
                            <td class="px-3 py-3 font-medium text-gray-900 dark:text-white">{{ $duty['judul'] }}</td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $duty['uraian'] }}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-gray-700 dark:text-gray-200">{{ $duty['akses'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Batasan dan keamanan</x-slot>
        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <p>Petugas tidak diberi izin menghapus data operasional, membuat atau mengubah akun, maupun mengelola role dan permission.</p>
            <p>Keputusan medis, kelayakan donor, serta pelepasan kantong darah harus mengikuti prosedur dan persetujuan tenaga kesehatan yang berwenang. Panel ini hanya membantu pencatatan dan alur kerja.</p>
            <p>Aplikasi mobile tetap menggunakan API Laravel yang ada. Perubahan jobdesk ini mengatur izin pengguna di backend, bukan mengubah alamat endpoint.</p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
