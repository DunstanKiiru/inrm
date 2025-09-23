<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Control;
use App\Models\ControlCategory;

class ControlController extends Controller
{
    public function index(Request $r)
    {
        $q = Control::with(['category', 'owner'])->orderBy('title');

        if ($r->filled('status')) {
            $q->where('status', $r->status);
        }
        if ($r->filled('category_id')) {
            $q->where('category_id', $r->category_id);
        }
        if ($r->filled('owner_id')) {
            $q->where('owner_id', $r->owner_id);
        }
        if ($s = $r->query('q')) {
            $q->where('title', 'ilike', "%$s%");
        }

        return $q->paginate(20);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:control_categories,id',
            'nature'      => 'nullable|string',
            'type'        => 'nullable|string',
            'frequency'   => 'nullable|string',
            'owner_id'    => 'nullable|exists:users,id',
            'status'      => 'nullable|string',
        ]);

        return Control::create($data);
    }

    public function show(Control $control)
    {
        return $control->load(['category', 'owner', 'testPlans.executions']);
    }

    public function update(Request $r, Control $control)
    {
        $control->update($r->all());

        return $control->fresh()->load(['category', 'owner']);
    }

    public function destroy(Control $control)
    {
        $control->delete();

        return response()->noContent();
    }

    // Categories quick endpoints (optional)
    public function categories()
    {
        return ControlCategory::orderBy('name')->get();
    }

    public function storeCategory(Request $r)
    {
        $data = $r->validate([
            'name'        => 'required',
            'description' => 'nullable',
        ]);

        return ControlCategory::create($data);
    }
}
