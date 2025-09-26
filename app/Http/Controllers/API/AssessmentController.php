<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Assessment;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentRound;
use App\Models\AssessmentResponse;

class AssessmentController extends Controller
{
    /**
     * List assessments for a vendor (TPR use-case)
     */
    public function vendorIndex($vendorId)
    {
        return Assessment::with('template')
            ->where('entity_type', 'vendor')
            ->where('entity_id', $vendorId)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Start a vendor assessment (TPR style)
     */
    public function start($vendorId, Request $r)
    {
        $data = $r->validate([
            'template_id' => 'required|exists:assessment_templates,id',
            'due_at' => 'nullable|date'
        ]);

        $ass = Assessment::create([
            'template_id' => $data['template_id'],
            'entity_type' => 'vendor',
            'entity_id' => $vendorId,
            'title' => 'Vendor Assessment',
            'status' => 'sent',
        ]);

        AssessmentRound::create([
            'assessment_id' => $ass->id,
            'round_no' => 1,
            'due_at' => $data['due_at'] ?? now()->addDays(14),
            'status' => 'pending',
        ]);

        return $ass->load('rounds');
    }

    /**
     * Show assessment with template + rounds + responses
     */
    public function show(Assessment $assessment)
    {
        return $assessment->load('template', 'rounds.responses');
    }

    /**
     * Submit responses for a round
     */
    public function submitResponses(Request $r, AssessmentRound $round)
    {
        $data = $r->validate([
            'answers_json' => 'required|json',
            'status' => 'nullable|string',
        ]);

        $resp = AssessmentResponse::create([
            'round_id' => $round->id,
            'submitted_by' => $r->user()->id ?? null,
            'answers_json' => $data['answers_json'],
            'status' => $data['status'] ?? 'submitted',
        ]);

        $round->update(['status' => 'in_progress']);
        return $resp;
    }

    /**
     * Score an assessment (average of numeric scores in responses)
     */
    public function score(Assessment $assessment)
    {
        $rows = AssessmentResponse::whereHas('round', fn($q) => $q->where('assessment_id', $assessment->id))
            ->get();

        $allScores = [];
        foreach ($rows as $row) {
            $answers = json_decode($row->answers_json, true);
            if (is_array($answers)) {
                foreach ($answers as $ans) {
                    if (isset($ans['score'])) {
                        $allScores[] = (float)$ans['score'];
                    }
                }
            }
        }

        $score = count($allScores) ? round(array_sum($allScores) / count($allScores), 1) : null;

        $assessment->update([
            'score' => $score,
            'status' => 'scored',
            'completed_at' => now(),
        ]);

        // Optional vendor residual update
        if ($assessment->entity_type === 'vendor' && $score !== null) {
            DB::table('tpr_vendors')->where('id', $assessment->entity_id)
                ->update(['residual_score' => $score, 'updated_at' => now()]);
        }

        return ['score' => $score];
    }
}
