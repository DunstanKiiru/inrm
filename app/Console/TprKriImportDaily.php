<?php
namespace App\Console;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TprKriImportDaily extends Command
{
    protected $signature = 'inrm:tpr-kri-import {--path=}';
    protected $description = 'Import vendor KRI CSVs from a directory (default from config).';

    public function handle(): int
    {
        $dir = $this->option('path') ?: config('inrm_tpr.kri.daily_import_path');
        if (!is_dir($dir)) { $this->warn('No directory: '.$dir); return 0; }
        $files = glob($dir.'/*.csv') ?: [];
        $n=0;
        foreach ($files as $file) {
            $this->info('Importing '.$file);
            $csv = file_get_contents($file);
            $lines = preg_split('/\r?\n/', trim($csv));
            $header = null;
            foreach ($lines as $line) {
                if ($line === '') continue;
                $cols = str_getcsv($line);
                if (!$header) { $header = array_map('trim',$cols); continue; }
                $row = array_combine($header, $cols);
                if (!$row) continue;
                $vendorId = (int)($row['vendor_id'] ?? 0);
                if ($vendorId <= 0) continue;
                DB::table('tpr_vendor_kri_measures')->insert([
                    'vendor_id'=>$vendorId,'kri_id'=>null,'kri_code'=>$row['kri_code'] ?? '',
                    'measured_at'=> $row['measured_at'] ?? now(),
                    'value'=> isset($row['value']) ? (float)$row['value'] : null,
                    'status'=> $row['status'] ?? null,
                    'meta'=> json_encode(['kri_name'=>$row['kri_name'] ?? null]),
                    'created_at'=> now(), 'updated_at'=> now()
                ]);
                if (in_array($row['status'] ?? '', config('inrm_tpr.kri.alert_statuses', ['alert','breach'])) && \Illuminate\Support\Facades\Schema::hasTable('rim_events')) {
                    DB::table('rim_events')->insert([
                        'type'=>'TPR_KRI_ALERT','severity'=>'medium','risk_id'=>null,
                        'message'=>'TPR KRI alert vendor_id='.$vendorId,
                        'occurred_at'=> now(), 'metrics'=> json_encode(['vendor_id'=>$vendorId,'kri_code'=>$row['kri_code'] ?? null,'value'=>$row['value'] ?? null]),
                        'created_at'=>now(),'updated_at'=>now()
                    ]);
                }
                $n++;
            }
            // optional move/cleanup can be added here
        }
        $this->info('Imported rows: '.$n);
        return 0;
    }
}
