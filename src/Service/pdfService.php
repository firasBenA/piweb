<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class pdfService
{
    private Dompdf $domPdf;

    public function __construct()
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Garamond');

        $this->domPdf = new Dompdf($pdfOptions);
    }

    public function showPdfFile(string $html): void
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        $this->domPdf->stream("details.pdf", [
            'Attachment' => false // Fix typo
        ]);
    }

    public function generateBinaryPdf(string $html): string
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        return $this->domPdf->output(); // Return the generated PDF content
    }
}
