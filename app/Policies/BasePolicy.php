<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Str;

class BasePolicy
{
    public function allow(User $user, $model, $ability): bool
    {
        // Ambil nama model, contoh: DailyReportLeads → daily_report_leads
        $modelName = Str::snake(class_basename($model));
        $changeCharacterModel = Str::replace('_', '.', $modelName);
        // Bentuk permission dinamis: daily_report_leads.view
        $permissions = "{$changeCharacterModel}.{$ability}";
        // dd($permissions);
        return $user->can($permissions);
    }

    public function __call($method, $arguments)
    {
        // method: view, create, update, delete, import, export, dll
        $user = $arguments[0];
        $model = $arguments[1] ?? new $this->model;
        return $this->allow($user, $model, $method);
    }
}
