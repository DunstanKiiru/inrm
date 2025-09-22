<?php
namespace App\Services;
class ClamavScanner {
  public function scan(string $path): string {
    if(!config('evidence.clamav.enabled')) return 'unknown';
    $sock=@fsockopen(config('evidence.clamav.host','127.0.0.1'), (int)config('evidence.clamav.port',3310), $e,$s,2.0);
    if(!$sock) return 'unknown';
    fwrite($sock, "SCAN {$path}\n");
    $resp=fgets($sock); fclose($sock);
    if($resp && str_contains($resp,'FOUND')) return 'infected';
    if($resp && str_contains($resp,'OK')) return 'clean';
    return 'unknown';
  }
}