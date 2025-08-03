<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;

class UsersImport implements ToModel
{

    protected $fieldMapping;

    public function __construct($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $mapped = [];
        foreach ($this->fieldMapping as $key => $mapping) {
            $mapped[$key] = $row[$mapping['column_index']];
        }
        // ensure password is hashed
        if (isset($mapped['password'])) {
            $mapped['password'] = bcrypt($mapped['password']);
        }
        // ensure role_id is set
        if (!isset($mapped['role_id'])) {
            $mapped['role_id'] = Role::where('name', 'guest')->first()->id;
        }
        return new User($mapped);
    }
}
