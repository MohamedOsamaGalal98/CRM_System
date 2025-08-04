<?php

namespace App\Filament\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class UserExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->query ? $this->query->get() : User::with(['roles'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Roles',
            'Is Active',
            'Email Verified',
            'Email Verified At',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param User $user
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '),
            $user->is_active ? 'Yes' : 'No',
            $user->email_verified_at ? 'Yes' : 'No',
            $user->email_verified_at ? $user->email_verified_at->setTimezone('Africa/Cairo')->format('Y-m-d H:i:s') : '',
            $user->created_at->setTimezone('Africa/Cairo')->format('Y-m-d H:i:s'),
            $user->updated_at->setTimezone('Africa/Cairo')->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
