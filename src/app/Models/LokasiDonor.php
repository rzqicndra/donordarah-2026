<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LokasiDonor extends Model
{
    use HasFactory;

    protected $table = 'lokasi_donor';

    protected $guarded = [];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'kapasitas_harian' => 'integer',
        'aktif' => 'boolean',
    ];

    /**
     * Buat slug unik saat lokasi baru disimpan tanpa mengubah slug yang sudah ada.
     */
    protected static function booted(): void
    {
        static::creating(function (self $lokasi): void {
            if (filled($lokasi->slug)) {
                return;
            }

            $nama = (string) ($lokasi->nama ?? $lokasi->nama_lokasi ?? '');
            $dasar = Str::slug($nama);
            $dasar = $dasar !== '' ? $dasar : 'lokasi-donor';
            $dasar = substr($dasar, 0, 240);

            $slug = $dasar;
            $nomor = 2;

            // Query ini juga mencakup baris soft-deleted karena model ini
            // tidak menggunakan trait SoftDeletes.
            while (self::query()->where('slug', $slug)->exists()) {
                $slug = $dasar . '-' . $nomor;
                $nomor++;
            }

            $lokasi->slug = $slug;
        });
    }

    public function jadwalDonors(): HasMany
    {
        return $this->hasMany(
            JadwalDonor::class,
            'lokasi_donor_id'
        );
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(
            JadwalDonor::class,
            'lokasi_donor_id'
        );
    }

    public function getNamaTampilanAttribute(): string
    {
        return (string) (
            $this->nama
            ?? $this->nama_lokasi
            ?? 'Lokasi Donor'
        );
    }

    public function getAlamatTampilanAttribute(): string
    {
        return (string) (
            $this->alamat
            ?? $this->alamat_lengkap
            ?? '-'
        );
    }

    public function getWilayahTampilanAttribute(): string
    {
        $wilayah = collect([
            $this->kota
                ?? $this->kabupaten
                ?? null,

            $this->provinsi
                ?? null,
        ])
            ->filter()
            ->implode(', ');

        return $wilayah !== '' ? $wilayah : '-';
    }

    public function getKontakTampilanAttribute(): string
    {
        return (string) (
            $this->nomor_telepon
            ?? $this->telepon
            ?? $this->kontak
            ?? '-'
        );
    }

    public function getGoogleMapsTampilanAttribute(): string
    {
        if (filled($this->url_google_maps)) {
            return (string) $this->url_google_maps;
        }

        if (
            filled($this->latitude)
            && filled($this->longitude)
        ) {
            return 'https://www.google.com/maps/search/?api=1&query='
                . rawurlencode(
                    $this->latitude . ',' . $this->longitude
                );
        }

        return 'https://www.google.com/maps/search/?api=1&query='
            . rawurlencode(
                collect([
                    $this->nama_tampilan,
                    $this->alamat_tampilan,
                    $this->wilayah_tampilan,
                ])
                    ->filter(fn (string $value): bool => $value !== '-')
                    ->implode(', ')
            );
    }
}