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
            // RFC 4180 escaping (quotes doubled, no escape character), which the
            // door system's csv-parse expects. PHP's "\\" escape wrote names
            // containing \" in a form it rejects, which stalled the whole update.
            $writeRow = function (array $row) use ($out) {
                fputcsv($out, $row, ',', '"', '');
            };
            // Always sent, so the body is never empty: the door system ignores an
            // empty body and keeps its previous list, but a list with no fobs must
            // take effect (e.g. a lockdown whose roles hold none)
            $writeRow(['key_id', 'announce_name', 'id']);
            foreach ($keyfobs as $line) {
                $writeRow($line);
            }
            fclose($out);
        }, $filename, $headers);
    }
}
