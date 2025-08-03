<?php

namespace App\Http\Controllers\Auth;

use App\Exports\UsersExport;
use App\Http\Controllers\Auth\BaseCrudController;
use App\Models\User;
use App\Traits\ReusableFunctions;
use Illuminate\Validation\Rule;

class UserController extends BaseCrudController
{
    use ReusableFunctions;

    /**
     * Get the model class
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Get the view path
     */
    protected function getViewPath(): string
    {
        return 'auth/User';
    }

    /**
     * Get search fields
     */
    protected function getSearchFields(): array
    {
        return ['name', 'email'];
    }

    /**
     * Get relationships to load
     */
    protected function getRelationships(): array
    {
        return ['role'];
    }

    /**
     * Get validation rules for create
     */
    protected function getCreateValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',
            'role_id' => 'nullable|exists:roles,id',
        ];
    }

    /**
     * Get validation rules for update
     */
    protected function getUpdateValidationRules($id): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
            'role_id' => 'nullable|exists:roles,id',
        ];
    }

    /**
     * Transform create data
     */
    protected function transformCreateData(array $data): array
    {
        $data['password'] = bcrypt($data['password']);
        unset($data['confirm_password']);
        return $data;
    }

    /**
     * Transform update data
     */
    protected function transformUpdateData(array $data): array
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['password_confirmation']);
        return $data;
    }

    /**
     * Get export class
     */
    protected function getExportClass(): string
    {
        return UsersExport::class;
    }

    /**
     * Get import field mapping
     */
    protected function getImportFieldMapping(): array
    {
        return [
            'name' => ['label' => 'Full Name', 'required' => true],
            'email' => ['label' => 'Email Address', 'required' => true],
            'password' => ['label' => 'Password', 'required' => false],
            'role_id' => ['label' => 'Role ID', 'required' => false],
            'email_verified_at' => ['label' => 'Email Verified', 'required' => false],
        ];
    }

    protected function getRequiredImportFields(): array
    {
        return ['name', 'email']; // Users require name and email
    }
}
