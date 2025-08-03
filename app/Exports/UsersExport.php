<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromQuery, WithHeadings
{
    use Exportable;

    protected $ids = [];
    protected $fields = [];

    public function __construct(array $ids = [], array $fields = [])
    {
        $this->ids = $ids;
        $this->fields = $fields;
    }

    public function query()
    {
        return User::query()
            ->whereIn('id', $this->ids)
            ->with('role')
            ->select($this->fields ?: ['id', 'name', 'email', 'role_id']);
    }
    public function headings(): array
    {
        // Generate headings based on selected fields
        $fields = $this->fields ?: ['id', 'name', 'email', 'role_id'];

        return $fields;
    }
}
