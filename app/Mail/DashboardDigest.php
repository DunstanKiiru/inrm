<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Dashboard;

class DashboardDigest extends Mailable {
  use Queueable, SerializesModels;
  public $dashboard;
  public function __construct(Dashboard $dashboard){ $this->dashboard = $dashboard; }
  public function build(){
    return $this->subject('Dashboard Digest: '.$this->dashboard->title)
      ->view('emails.digest', ['dashboard'=>$this->dashboard]);
  }
}
