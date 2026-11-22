<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use App\Support\JobdeskAccess;
use Filament\Pages\Page;

class PanduanTugas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Tugas & Hak Akses';

    protected static ?string $title = 'Tugas & Hak Akses';

    protected static ?string $navigationGroup = 'Panduan';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.panduan-tugas';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'petugas']);
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $user = auth()->user();

        return [
            'isSuperAdmin' => $user instanceof User && $user->hasRole('super_admin'),
            'staffDuties' => JobdeskAccess::staffDuties(),
        ];
    }
}
