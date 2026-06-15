<?php

namespace BB\Http\Controllers;

use BB\Entities\KeyFob;
use Illuminate\Http\Request;

class KeyFobCsvController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $keyfobs = KeyFob::active()
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->active();
            })
            ->get()
            ->map(function ($keyfob) {
                return [
                    'key_id' => $keyfob->key_id,
                    'announce_name' => $keyfob->user->announce_name,
                    'id' => $keyfob->user->id,
                ];
            });


        $filename = 'keyfobs_' . time() . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        return response()->streamDownload(function () use ($keyfobs) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            $firstRow = $keyfobs->first();
            if ($firstRow !== null) {
                fputcsv($out, array_keys($firstRow));
            }
            foreach ($keyfobs as $line) {
                fputcsv($out, $line);
            }
            fclose($out);
        }, $filename, $headers);
    }
}
