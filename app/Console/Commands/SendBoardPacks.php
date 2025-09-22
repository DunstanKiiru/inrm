<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use App\Mail\BoardPackMail;

class SendBoardPacks extends Command
{
    protected $signature = 'boardpack:send {--to=}';
    protected $description = 'Generate board pack (PDF if dompdf installed) and email';

    public function handle()
    {
        $kpis = [
            'risks_total' => (int) DB::table('risks')->count(),
            'risks_active' => (int) DB::table('risks')->where('status','active')->count(),
            'issues_open' => (int) DB::table('issues')->where('status','open')->count(),
            'policies_published' => (int) DB::table('policies')->where('status','published')->count(),
        ];

        $html = View::make('reports.boardpack_pdf', ['kpis'=>$kpis])->render();

        // Try PDF via Dompdf if available
        $pdfData = null;
        try {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
                $pdfData = $pdf->output();
                $filename = 'reports/boardpack-'.date('Y-m').'.pdf';
                Storage::disk('local')->put($filename, $pdfData);
                $this->info('PDF generated and saved to storage/app/'.$filename);
            } else {
                $this->warn('Dompdf not installed. Sending HTML email only.');
            }
        } catch (\Throwable $e) {
            $this->error('PDF generation failed: '.$e->getMessage());
        }

        $to = $this->option('to') ?: env('BOARD_PACK_RECIPIENTS','');
        $recipients = array_filter(array_map('trim', explode(',', $to)));
        if (empty($recipients)) {
            $this->warn('No recipients configured. Set --to or BOARD_PACK_RECIPIENTS.');
            return 0;
        }

        foreach ($recipients as $rcpt) {
            Mail::to($rcpt)->send(new BoardPackMail($kpis, $pdfData));
        }
        $this->info('Board pack sent to: '.implode(', ', $recipients));
        return 0;
    }
}
