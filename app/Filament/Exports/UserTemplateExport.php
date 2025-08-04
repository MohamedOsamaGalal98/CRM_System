<?php

namespace App\Filament\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            [
                'John Doe',
                'john.doe@example.com',
                'password123',
                'Admin,Sales',
                'Yes',
                'Yes'
            ],
            [
                'Jane Smith',
                'jane.smith@example.com',
                'password456',
                'Sales',
                'Yes',
                'No'
            ],
            [
                'Update User',
                'existing@example.com',
                '',
                'Admin',
                'Yes',
                'Yes'
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Name',
            'Email', 
            'Password',
            'Roles (comma separated)',
            'Is Active (Yes/No/1/0)',
            'Email Verified (Yes/No/1/0)',
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
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '4472C4']]],
            // Style example rows with light background
            2 => ['fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E7F3FF']]],
            3 => ['fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E7F3FF']]],
        ];
    }
}
