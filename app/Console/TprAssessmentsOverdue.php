<?php
namespace App\Console;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TprAssessmentsOverdue extends Command
{
    protected $signature = 'inrm:tpr-assess-overdue';
    protected $description = 'Flag overdue vendor assessments (optional RIM events).';

    public function handle(): int
    {
        $today = now()->toDateString();
        $rows = DB::table('tpr_assessments')->whereNull('completed_at')->where('due_at','<',$today)->get();
        $n=0;
        foreach ($rows as $a) {
            DB::table('tpr_assessments')->where('id',$a->id)->update(['status'=>'in_progress','updated_at'=>now()]);
            if (config('inrm_tpr.rim_events_enabled') && \Illuminate\Support\Facades\Schema::hasTable('rim_events')) {
                DB::table('rim_events')->insert([
                    'type'=>'TPR_ASSESSMENT_OVERDUE','severity'=>'medium','risk_id'=>null,
                    'message'=>'Vendor assessment overdue id='.$a->id,
                    'occurred_at'=> now(), 'metrics'=> json_encode(['assessment_id'=>$a->id,'vendor_id'=>$a->vendor_id]),
                    'created_at'=> now(), 'updated_at'=> now()
                ]);
            }
            $n++;
        }
        $this->info('Overdue assessments flagged: '.$n);
        return 0;
    }
}
