<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kri;
use App\Models\KriReading;
use App\Models\KriBreach;

class KriController extends Controller
{
    public function index(Request $r)
    {
        $q = Kri::query()->orderBy('title');
        if ($r->filled('entity_type')) $q->where('entity_type', $r->entity_type);
        if ($r->filled('entity_id')) $q->where('entity_id', $r->entity_id);
        return $q->paginate(20);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => 'required',
            'description' => 'nullable',
            'entity_type' => 'required|in:risk,org_unit',
            'entity_id' => 'required|integer',
            'unit' => 'nullable',
            'cadence' => 'required|in:weekly,monthly,quarterly',
            'target' => 'nullable|numeric',
            'warn_threshold' => 'nullable|numeric',
            'alert_threshold' => 'nullable|numeric',
            'direction' => 'required|in:higher_is_better,lower_is_better'
        ]);
        return Kri::create($data);
    }

    public function show(Kri $kri)
    {
        return $kri->load('readings','breaches');
    }

    public function update(Request $r, Kri $kri)
    {
        $kri->update($r->all());
        return $kri->fresh();
    }

    public function destroy(Kri $kri)
    {
        $kri->delete();
        return response()->noContent();
    }

    // Readings
    public function readings(Kri $kri)
    {
        return $kri->readings()->orderBy('collected_at','desc')->limit(100)->get();
    }

    public function addReading(Request $r, Kri $kri)
    {
        $data = $r->validate([
            'value' => 'required|numeric',
            'collected_at' => 'nullable|date',
            'source' => 'nullable|string'
        ]);
        $row = $kri->readings()->create($data);
        return $row;
    }

    public function breaches(Kri $kri)
    {
        return $kri->breaches()->orderByDesc('id')->limit(50)->get();
    }
}
