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

    /**
     * Create a new message instance.
     *
     * @param array $kpis
     * @param string|null $pdfData
     */
    public function __construct(array $kpis, ?string $pdfData = null)
    {
        $this->kpis = $kpis;
        $this->pdfData = $pdfData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mailable = $this->subject('Monthly Risk Board Pack')
                         ->view('reports.boardpack_pdf', [
                             'kpis' => $this->kpis,
                         ]);

        if ($this->pdfData) {
            $mailable->attachData(
                $this->pdfData,
                'boardpack.pdf',
                ['mime' => 'application/pdf']
            );
        }

        return $mailable;
    }
}
