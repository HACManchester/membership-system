<?php

namespace BB\Http\Controllers;

use BB\Entities\AccessLockdown;
use BB\Entities\KeyFob;
use Illuminate\Http\Request;

class KeyFobCsvController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $query = KeyFob::active()
            ->with('user')
            ->whereHas('user', function ($query) {
                /** @var \Illuminate\Database\Eloquent\Builder<\BB\Entities\User> $query */
                $query->active();
            });

        // While the space is locked down only members holding one of the lockdown's
        // roles keep their door access. Lifting it restores everyone on the next poll.
        $lockdown = AccessLockdown::current();
        if ($lockdown) {
            $query->whereHas('user.roles', function ($query) use ($lockdown) {
                $query->whereIn('name', $lockdown->roles);
            });
        }

        $keyfobs = $query->get()
            ->map(function ($keyfob) {
                return [
                    'key_id' => $keyfob->key_id,
                    'announce_name' => $keyfob->user->announce_name,
                    'id' => $keyfob->user->id,
                ];
            });


        $filename = 'keyfobs_' . time() . '.csv';
        $headers = [
            // The door system ignores the headers, but stating the charset keeps the
            // response stable: the framework's own casing of it varies by version
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        return response()->streamDownload(function () use ($keyfobs) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            // The door system parses this output, so fputcsv's quoting is spelled out:
            // PHP 8.4 deprecates relying on the $escape default, which will change
            $writeRow = function (array $row) use ($out) {
                fputcsv($out, $row, ',', '"', '\\');
            };
            $firstRow = $keyfobs->first();
            if ($firstRow !== null) {
                $writeRow(array_keys($firstRow));
            }
            foreach ($keyfobs as $line) {
                $writeRow($line);
            }
            fclose($out);
        }, $filename, $headers);
    }
}
