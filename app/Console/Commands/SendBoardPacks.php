<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use App\Mail\BoardPackMail;
use Dompdf\Dompdf;
use Dompdf\Options;

class SendBoardPacks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     *   php artisan boardpack:send --to=ceo@example.com,ciso@example.com
     */
    protected $signature = 'boardpack:send {--to=}';

    /**
     * The console command description.
     */
    protected $description = 'Generate board pack (PDF using Dompdf) and email it to recipients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1) Collect KPIs
        $kpis = [
            'risks_total'        => (int) DB::table('risks')->count(),
            'risks_active'       => (int) DB::table('risks')->where('status', 'active')->count(),
            'issues_open'        => (int) DB::table('issues')->where('status', 'open')->count(),
            'policies_published' => (int) DB::table('policies')->where('status', 'published')->count(),
        ];

        // 2) Render Blade view into HTML
        $html = View::make('reports.boardpack_pdf', ['kpis' => $kpis])->render();

        // 3) Generate PDF with Dompdf
        $pdfData = null;
        try {
            $options = new Options();
            $options->set('defaultFont', 'sans-serif');
            $options->set('isRemoteEnabled', true); // allow external assets like CSS/images

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfData = $dompdf->output();

            if ($pdfData) {
                $filename = 'reports/boardpack-' . date('Y-m') . '.pdf';
                Storage::disk('local')->put($filename, $pdfData);
                $this->info('✅ PDF generated and saved to storage/app/' . $filename);
            }
        } catch (\Throwable $e) {
            $this->error('❌ PDF generation failed: ' . $e->getMessage());
            return 1;
        }

        // 4) Resolve recipients
        $to = $this->option('to') ?: env('BOARD_PACK_RECIPIENTS', '');
        $recipients = array_filter(array_map('trim', explode(',', $to)));

        if (empty($recipients)) {
            $this->warn('⚠️  No recipients configured. Use --to or set BOARD_PACK_RECIPIENTS in .env');
            return 0;
        }

        // 5) Send emails
        foreach ($recipients as $rcpt) {
            Mail::to($rcpt)->send(new BoardPackMail($kpis, $pdfData));
        }

        $this->info('📧 Board pack sent to: ' . implode(', ', $recipients));
        return 0;
    }
}
