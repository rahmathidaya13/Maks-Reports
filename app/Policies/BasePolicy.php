<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Str;

class BasePolicy
{

    protected function getPermissionRoot($model)
    {
        // ambil model dari contoh DailyReportModel,jika nama model DailyReportModel maka
        // akan di ubah menjadi daily.report.leads sesuai dengan map yang dibuat
        $class = is_string($model) ? $model : $model::class;

        $map = config('permission_map'); // map nama model lebih clean

        if (isset($map[$class])) {
            return $map[$class]; // contoh: report.leads
        }

        // fallback → default menggunakan nama model seperti sebelumnya
        $modelName = Str::snake(class_basename($model));
        return Str::replace('_', '.', $modelName);
    }
    public function allow(User $user, $model, $ability): bool
    {
        $modelName = $this->getPermissionRoot($model);

        // Bentuk permission dinamis: daily_report_leads.view
        $permissions = "{$modelName}.{$ability}";
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
