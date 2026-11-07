<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait ManagesMasterData
{
    private function masterPage(array $cfg, $editing = null)
    {
        $rows = $cfg['model']::withCount($cfg['usage'])->orderBy($cfg['field'])->get();

        return view($cfg['view'], compact('rows', 'editing'));
    }

    private function masterStore(Request $request, array $cfg)
    {
        $cfg['model']::create($this->masterValidate($request, $cfg));

        return redirect()->route($cfg['base'] . '.index')->with('success', $cfg['label'] . ' berhasil ditambahkan.');
    }

    private function masterUpdate(Request $request, array $cfg, $row)
    {
        $row->update($this->masterValidate($request, $cfg, $row));

        return redirect()->route($cfg['base'] . '.index')->with('success', $cfg['label'] . ' berhasil diperbarui.');
    }

    private function masterDestroy(array $cfg, $row)
    {
        $count = $row->{$cfg['usage']}()->count();

        if ($count > 0) {
            return back()->with('error', "{$cfg['label']} tidak bisa dihapus karena masih dipakai oleh {$count} {$cfg['usageLabel']}.");
        }

        $row->delete();

        return redirect()->route($cfg['base'] . '.index')->with('success', $cfg['label'] . ' berhasil dihapus.');
    }

    private function masterValidate(Request $request, array $cfg, $ignore = null): array
    {
        $model = new $cfg['model']();

        return $request->validate([
            $cfg['field'] => [
                'required', 'string', 'max:100',
                Rule::unique($model->getTable(), $cfg['field'])
                    ->where('id_business', $this->businessId())
                    ->ignore($ignore?->getKey(), $model->getKeyName()),
            ],
        ], [], [$cfg['field'] => 'nama']);
    }
}
