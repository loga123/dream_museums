<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Marker;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use PDF;

class ExportController extends Controller
{

    public function generatePDF(Group $group)
    {
        $markers = $group->markers->toArray();

        $html = '<html><body>';
        foreach($markers as $obj) {
            $content = $this->htmlNameDescription($obj) . '<br><br>';
            $content = $content . $this->htmlImage($obj);
            $html = $html . $this->htmlPageBreak($content);
        }
        $html = $html . '</body></html>';
        $pdf = PDF::loadHTML($html);

        return $pdf->download('markers.pdf');
    }


    public function generatePdfWithText(Marker $marker)
    {
        $html = '<html><body>';
        $html = $html . $this->htmlNameDescription($marker);
        $html = $html . $this->htmlText($marker);
        $html = $html . '</body></html>';
        $pdf = PDF::loadHTML($html);

        return $pdf->download($marker['name'].'.pdf');
    }

    private function htmlNameDescription($marker) {
        $html = '<h1>' . $marker['name'] . '</h1>';
        return $html . '<p>' . $marker['description'] . '</h1>';
    }

    private function htmlText($marker) {
        return '<p>' . $marker['text'] . '</p>';
    }

    private function htmlImage($marker) {
        $img = public_path($marker['image_marker']);
        return '<img src="' . $img . '">';
    }

    private function htmlPageBreak($html) {
        $div = '<div style="break-after: always; page-break-after: always;">' . $html . '</div>';
        return $div;
    }
}
