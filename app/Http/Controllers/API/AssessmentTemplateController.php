<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AssessmentTemplate;

class AssessmentTemplateController extends Controller
{
    /**
     * List templates.
     * Supports both TPR (`tpr_assessment_templates`) and generic (`assessment_templates`).
     */
    public function index(Request $r)
    {
        if ($r->get('scope') === 'tpr') {
            return DB::table('tpr_assessment_templates')->orderBy('code')->get();
        }
        return AssessmentTemplate::orderBy('title')->get();
    }

    /**
     * Create template.
     * - For TPR scope: includes discrete questions array.
     * - For generic: uses schema_json approach.
     */
    public function store(Request $r)
    {
        if ($r->get('scope') === 'tpr') {
            $data = $r->validate([
                'code' => 'required',
                'title' => 'required',
                'description' => 'nullable',
                'questions' => 'array',
            ]);

            $tid = DB::table('tpr_assessment_templates')->insertGetId([
                'code' => $data['code'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (($data['questions'] ?? []) as $q) {
                DB::table('tpr_assessment_questions')->insert([
                    'template_id' => $tid,
                    'code' => $q['code'] ?? null,
                    'question' => $q['question'],
                    'type' => $q['type'] ?? 'text',
                    'options' => json_encode($q['options'] ?? null),
                    'weight' => $q['weight'] ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return DB::table('tpr_assessment_templates')->where('id', $tid)->first();
        }

        // Generic template
        $data = $r->validate([
            'title' => 'required',
            'description' => 'nullable',
            'entity_type' => 'required|in:risk,org_unit',
            'schema_json' => 'required',
            'ui_schema_json' => 'nullable',
            'status' => 'nullable',
        ]);

        return AssessmentTemplate::create($data);
    }

    /**
     * Show template by id or code.
     */
    public function show($id, Request $r)
    {
        if ($r->get('scope') === 'tpr') {
            $t = DB::table('tpr_assessment_templates')
                ->where('id', $id)->orWhere('code', $id)->first();

            if (!$t) {
                return response()->json(['error' => 'Not found'], 404);
            }

            $qs = DB::table('tpr_assessment_questions')
                ->where('template_id', $t->id)
                ->orderBy('id')
                ->get();

            return ['template' => $t, 'questions' => $qs];
        }

        return AssessmentTemplate::findOrFail($id);
    }

    /**
     * Update generic template only.
     */
    public function update(Request $r, AssessmentTemplate $template)
    {
        $template->update($r->all());
        return $template->fresh();
    }

    /**
     * Delete generic template only.
     */
    public function destroy(AssessmentTemplate $template)
    {
        $template->delete();
        return response()->noContent();
    }
}
