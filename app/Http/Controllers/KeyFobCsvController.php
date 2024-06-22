<?php

namespace BB\Http\Controllers;

use BB\Entities\KeyFob;
use Illuminate\Http\Request;

class KeyFobCsvController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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

        return response()->streamDownload(function () use ($keyfobs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_keys($keyfobs[0]));
            foreach ($keyfobs as $line) {
                fputcsv($out, $line);
            }
            fclose($out);
        }, 'keyfobs_' . time() . '.csv');
    }
}
