<?php

namespace App\Support;

final class JobdeskAccess
{
    /**
     * Izin CRUD operasional yang diperbolehkan untuk petugas.
     * Kebijakan penghapusan: hanya Super Admin.
     *
     * @var array<string, list<string>>
     */
    public const STAFF_RESOURCES = [
        'lokasi::donor' => ['view_any', 'view', 'create', 'update'],
        'jadwal::donor' => ['view_any', 'view', 'create', 'update'],
        'pendaftaran::donor' => ['view_any', 'view', 'create', 'update'],
        'pemeriksaan::kesehatan' => ['view_any', 'view', 'create', 'update'],
        'kantong::darah' => ['view_any', 'view', 'create', 'update'],
        'permintaan::darah' => ['view_any', 'view', 'update'],
        'item::permintaan::darah' => ['view_any', 'view', 'create', 'update'],
        'distribusi::darah' => ['view_any', 'view', 'create', 'update'],
        'profil::pendonor' => ['view_any', 'view'],
        'profil::rumah::sakit' => ['view_any', 'view', 'update'],
        'user' => ['view_any', 'view'],
    ];

    /** @var list<string> */
    public const STAFF_PAGES = [
        'page_LaporanOperasional',
        'page_PanduanTugas',
    ];

    /** @var list<string> */
    public const STAFF_WIDGETS = [
        'widget_RingkasanOperasional',
        'widget_StokDarahPerGolongan',
        'widget_PermintaanDarahTerbaru',
        'widget_KantongMendekatiKedaluwarsa',
    ];

    /**
     * @return list<string>
     */
    public static function staffPermissions(): array
    {
        $permissions = [
            ...self::STAFF_PAGES,
            ...self::STAFF_WIDGETS,
        ];

        foreach (self::STAFF_RESOURCES as $resource => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$action}_{$resource}";
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * @return list<string>
     */
    public static function managedPermissions(): array
    {
        $permissions = self::staffPermissions();
        $resources = [
            ...array_keys(self::STAFF_RESOURCES),
            'role',
            'activity',
            'token',
        ];

        foreach ($resources as $resource) {
            foreach (['view_any', 'view', 'create', 'update', 'delete', 'delete_any'] as $action) {
                $permissions[] = "{$action}_{$resource}";
            }
        }

        return array_values(array_unique($permissions));
    }

    /**
     * Deskripsi jobdesk yang tampil di panel Filament.
     *
     * @return list<array{judul:string, uraian:string, akses:string}>
     */
    public static function staffDuties(): array
    {
        return [
            [
                'judul' => 'Lokasi & jadwal donor',
                'uraian' => 'Mencatat lokasi, membuat jadwal, dan memperbarui informasi kegiatan donor.',
                'akses' => 'Lihat, tambah, ubah',
            ],
            [
                'judul' => 'Pendaftaran pendonor',
                'uraian' => 'Memeriksa pendaftar, mencatat kehadiran, dan memproses status pendaftaran.',
                'akses' => 'Lihat, tambah, ubah',
            ],
            [
                'judul' => 'Pemeriksaan kesehatan',
                'uraian' => 'Mencatat hasil pemeriksaan yang sudah ditetapkan petugas kesehatan berwenang.',
                'akses' => 'Lihat, tambah, ubah',
            ],
            [
                'judul' => 'Stok & kantong darah',
                'uraian' => 'Menginput dan memperbarui status kantong berdasarkan proses verifikasi yang sah.',
                'akses' => 'Lihat, tambah, ubah',
            ],
            [
                'judul' => 'Permintaan & distribusi darah',
                'uraian' => 'Menangani permintaan, alokasi stok, serta pencatatan penyiapan dan distribusi.',
                'akses' => 'Lihat, tambah, ubah',
            ],
            [
                'judul' => 'Pendonor, rumah sakit & akun',
                'uraian' => 'Melihat data pendonor dan pengguna; memperbarui profil rumah sakit sesuai kewenangan.',
                'akses' => 'Terbatas sesuai modul',
            ],
            [
                'judul' => 'Laporan operasional',
                'uraian' => 'Memantau ringkasan pendaftaran, stok, permintaan, dan distribusi.',
                'akses' => 'Lihat & ekspor',
            ],
        ];
    }
}
