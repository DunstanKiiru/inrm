<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kri;
use App\Models\KriBreach;

class KriEvaluateBreaches extends Command
{
    protected $signature = 'kri:evaluate';
    protected $description = 'Evaluate KRI readings against thresholds and create breach entries';

    public function handle()
    {
        $count = 0;

        foreach (Kri::with('readings')->get() as $kri) {
            $reading = $kri->readings()->orderBy('collected_at', 'desc')->first();
            if (!$reading) continue;

            $val = (float) $reading->value;
            $warn = $kri->warn_threshold;
            $alert = $kri->alert_threshold;
            $level = null;

            if ($kri->direction === 'higher_is_better') {
                if ($val < $alert && $alert !== null) {
                    $level = 'alert';
                } elseif ($val < $warn && $warn !== null) {
                    $level = 'warn';
                }
            } else { // lower_is_better
                if ($val > $alert && $alert !== null) {
                    $level = 'alert';
                } elseif ($val > $warn && $warn !== null) {
                    $level = 'warn';
                }
            }

            if ($level) {
                KriBreach::create([
                    'kri_id' => $kri->id,
                    'reading_id' => $reading->id,
                    'level' => $level,
                    'message' => "KRI '{$kri->title}' breached {$level} threshold with value {$val}",
                ]);
                $count++;
            }
        }

        $this->info('Breach evaluations created: ' . $count);
        return 0;
    }
}
