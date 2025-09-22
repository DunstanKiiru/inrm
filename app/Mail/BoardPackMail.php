<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BoardPackMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $kpis;
    protected ?string $pdfData = null;

    public function __construct(array $kpis, ?string $pdfData = null)
    {
        $this->kpis = $kpis;
        $this->pdfData = $pdfData;
    }

    public function build()
    {
        $m = $this->subject('Monthly Risk Board Pack')
                 ->view('reports.boardpack_pdf', ['kpis'=>$this->kpis]);

        if ($this->pdfData) {
            $m->attachData($this->pdfData, 'boardpack.pdf', ['mime'=>'application/pdf']);
        }
        return $m;
    }
}
