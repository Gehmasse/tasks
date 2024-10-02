<?php

namespace App;

use App\Exceptions\CalDavException;
use App\Exceptions\StatusCodeException;
use App\Jobs\DownloadTasks;
use App\Models\Calendar;
use App\Models\Remote;
use App\Models\Task;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

readonly class Client
{
    public static function syncNextPart(): JsonResponse
    {
        $calendars = 0;
        $errors = 0;

        foreach (Remote::all() as $remote) {
            try {
                $calendars += $remote->sync();
            } catch (StatusCodeException|ConnectionException $e) {
                report($e);
                $errors++;
                continue;
            }
        }

        if ($calendars > 0) {
            return Response::json(['finished' => false, 'message' => $calendars.' calendars must be updated; ' . $errors . ' error']);
        }

        return Response::json(['finished' => true, 'message' => 'finished sync; ' . $errors . ' error']);
    }

    /**
     * @throws StatusCodeException
     * @throws ConnectionException
     */
    public static function calendars(Remote $remote): Generator
    {
        $body = '<?xml version="1.0" encoding="utf-8" ?>
        <d:propfind xmlns:d="DAV:"
            xmlns:cs="http://calendarserver.org/ns/"
            xmlns:ical="http://apple.com/ns/ical/"
            xmlns:caldav="urn:ietf:params:xml:ns:caldav">
            <d:prop>
                <d:displayname />
                <cs:getctag />
                <ical:calendar-color />
                <caldav:supported-calendar-component-set />
            </d:prop>
        </d:propfind>';

        $response = Http::withBasicAuth($remote->username, $remote->password)
            ->withHeaders([
                'Depth' => '1',
                'Prefer' => 'return-minimal',
                'Content-Type' => 'application/xml; charset=utf-8',
                'Content-Length' => strlen($body),
            ])
            ->send('PROPFIND', $remote->href, ['body' => $body])
            ->body();

        $response = str_replace('<d:multistatus', '<d:multistatus xmlns:x1="http://apple.com/ns/ical/"', $response);

        $xml = simplexml_load_string($response);

        foreach ($xml->xpath('//d:response') as $calendar) {
            $status = (string) ($calendar->xpath('d:propstat/d:status')[0] ?? null);

            if (str_contains($status, '418')) {
                // skip 418 - I'm a teapot; used by nextcloud for root, trash etc.
                continue;
            }

            if (! str_contains($status, '200')) {
                throw new StatusCodeException($status);
            }

            $allowedItems = [];
            foreach ($calendar->xpath('d:propstat/d:prop/cal:supported-calendar-component-set/cal:comp') as $itemList) {
                foreach ($itemList->attributes() as $v) {
                    $allowedItems[] = (string) $v;
                }
            }

            if (! in_array('VTODO', $allowedItems)) {
                continue; // to next calendar if vtodo is not supported
            }

            yield (new Calendar)->fill([
                'remote_id' => $remote->id,
                'href' => (string) ($calendar->xpath('d:href')[0] ?? null),
                'ctag' => (string) ($calendar->xpath('d:propstat/d:prop/cs:getctag')[0] ?? null),
                'name' => (string) ($calendar->xpath('d:propstat/d:prop/d:displayname')[0] ?? null),
                'color' => (string) ($calendar->xpath('d:propstat/d:prop/x1:calendar-color')[0] ?? null),
            ]);
        }
    }

    /**
     * @throws ConnectionException
     */
    public static function tasks(Calendar $calendar, array $hrefs): Generator
    {
        $url = trim($calendar->full_href, '/').'/';

        $multi = collect($hrefs)
            ->map(fn (string $href) => '<d:href>'.$href.'</d:href>')
            ->join('');

        $body = '<c:calendar-multiget xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
            <d:prop>
                <d:getetag />
                <c:calendar-data />
            </d:prop>
            '.$multi.'
        </c:calendar-multiget>';

        $response = Http::withBasicAuth($calendar->remote->username, $calendar->remote->password)
            ->withHeaders([
                'Depth' => '1',
                'Prefer' => 'return-minimal',
                'Content-Type' => 'application/xml; charset=utf-8',
                'Content-Length' => strlen($body),
            ])
            ->send('REPORT', $url, ['body' => $body])
            ->body();

        $xml = simplexml_load_string($response);

        $xml->registerXPathNamespace('d', 'DAV:');
        $xml->registerXPathNamespace('cal', 'urn:ietf:params:xml:ns:caldav');

        foreach ($xml->xpath('//d:response') as $task) {
            if (! str_contains($status = (string) ($task->xpath('d:propstat/d:status')[0] ?? null), '200')) {
                report(new ConnectionException($status));

                continue;
            }

            yield (new Task)->fill([
                'calendar_id' => $calendar->id,
                'href' => (string) ($task->xpath('d:href')[0] ?? null),
                'etag' => (string) ($task->xpath('d:propstat/d:prop/d:getetag')[0] ?? null),
                'ical' => (string) ($task->xpath('d:propstat/d:prop/cal:calendar-data')[0] ?? null),
            ]);
        }
    }

    /**
     * @throws ConnectionException
     */
    public static function updateCalendar(Calendar $calendar, string $ctagOnSuccess): void
    {
        $locals = Task::query()
            ->where('calendar_id', $calendar->id)
            ->get(['href', 'etag'])
            ->keyBy('href');

        $remotes = self::etags($calendar);

        $diff = 0;

        $hrefs = [];

        foreach ($remotes as $href => $_) {
            // create calendar if not exists
            if (! $locals->has($href)) {
                $hrefs[] = $href;
                $diff++;

                continue;
            }

            // update calendar if ctag and so content has changed
            if ($locals[$href]->etag !== $remotes[$href]->etag) {
                $hrefs[] = $href;
                $diff++;
            }
        }

        // perform inline, not in background, because
        // whole operation already runs queued
        foreach (Client::tasks($calendar, hrefs: $hrefs) as $task) {
            $task->createOrUpdate();
        }

        // if nothing has changed, apply the ctag
        if ($diff === 0) {
            $calendar->ctag = $ctagOnSuccess;
            $calendar->save();
        }
    }

    /**
     * @throws CalDavException
     * @throws ConnectionException
     */
    public static function updateTask(Task $task): void
    {
        $remote = $task->calendar->remote;

        $response = Http::withBasicAuth($remote->username, $remote->password)
            ->withHeaders([
                'Content-Type' => 'text/calendar; charset=utf-8',
                'If-Match' => $task->etag,
                'Content-Length' => strlen($task->ical),
            ])
            // do not use ->put() here - the server doesn't like it
            ->send('PUT', $task->full_href, ['body' => $task->ical])
            ->body();

        if (trim($response) !== '') {
            $xml = simplexml_load_string($response);

            $xml->registerXPathNamespace('d', 'DAV:');

            // when if-match conditions is not satisfied, update the local task
            foreach ($xml->xpath('//d:error') as $error) {
                foreach ($error->xpath('//s:exception') as $exception) {
                    if (str_contains('Sabre\DAV\Exception\PreconditionFailed', $exception)) {
                        DownloadTasks::dispatch($task->calendar, [$task->href]);

                        return;
                    }
                }

                throw new CalDavException($response, $task->ical);
            }
        }

        DownloadTasks::dispatch($task->calendar, [$task->href]);
    }

    /**
     * @throws ConnectionException
     */
    private static function etags(Calendar $calendar): Collection
    {
        $url = trim($calendar->full_href, '/').'/';

        $body = '<c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
            <d:prop>
                <d:getetag />
            </d:prop>
            <c:filter>
                <c:comp-filter name="VCALENDAR">
                    <c:comp-filter name="VTODO" />
                </c:comp-filter>
            </c:filter>
        </c:calendar-query>';

        $response = Http::withBasicAuth($calendar->remote->username, $calendar->remote->password)
            ->withHeaders([
                'Depth' => '1',
                'Prefer' => 'return-minimal',
                'Content-Type' => 'application/xml; charset=utf-8',
                'Content-Length' => strlen($body),
            ])
            ->send('REPORT', $url, ['body' => $body])
            ->body();

        $xml = simplexml_load_string($response);

        $xml->registerXPathNamespace('d', 'DAV:');
        $xml->registerXPathNamespace('cal', 'urn:ietf:params:xml:ns:caldav');

        $etags = collect();

        foreach ($xml->xpath('//d:response') as $task) {
            if (! str_contains($status = (string) ($task->xpath('d:propstat/d:status')[0] ?? null), '200')) {
                report(new ConnectionException($status));

                continue;
            }

            $href = (string) ($task->xpath('d:href')[0] ?? null);
            $etag = (string) ($task->xpath('d:propstat/d:prop/d:getetag')[0] ?? null);

            $etags[$href] = (object) ['etag' => $etag];
        }

        return $etags;
    }
}
