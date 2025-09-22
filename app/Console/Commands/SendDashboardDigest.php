<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\DashboardDigest;
use App\Models\Dashboard;
use App\Models\User;

class SendDashboardDigest extends Command {
  protected $signature = 'dash:send-digest {--dashboard=} {--role=}';
  protected $description = 'Send scheduled dashboard digest email to users (optionally filter by role).';

  public function handle(){
    $dashboardId = $this->option('dashboard');
    $role = $this->option('role');
    $dash = $dashboardId ? Dashboard::find($dashboardId) : Dashboard::where('is_default',true)->first();
    if(!$dash){ $this->error('No dashboard found.'); return 1; }
    $users = User::query();
    if($role){ $users->where('role',$role); }
    $list = $users->get();
    foreach($list as $u){
      Mail::to($u->email)->send(new DashboardDigest($dash));
    }
    $this->info('Sent digest to '.$list->count().' users.');
    return 0;
  }
}
