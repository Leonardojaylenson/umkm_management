<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rule;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function businessId(): int
    {
        return (int) auth()->user()->id_business;
    }

    protected function existsInBusiness(string $table, string $column): Exists
    {
        return Rule::exists($table, $column)->where('id_business', $this->businessId());
    }
}
