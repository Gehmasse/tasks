<?php

namespace App;

use App\Exceptions\CalDavException;
use App\Exceptions\ConnectionException;
use App\Exceptions\StatusCodeException;
use App\Models\Calendar;
use App\Models\DownloadQueue;
use App\Models\Remote;
use App\Models\Task;
use App\Models\UploadQueue;
use Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

readonly class Client
{
    private function __construct(private Remote $remote) {}

    public static function new(Remote $remote): Client
    {
        return new Client($remote);
    }

    /**
     * @throws ConnectionException
     */
    public static function syncFull(): void
    {
        while (true) {
            $result = self::syncNextPart();

            if ($result['finished']) {
                return;
            }
        }
    }

    /**
     * @throws ConnectionException
     */
    public static function syncNextPart(int $total = 800): JsonResponse
    {
        $downloaded = DownloadQueue::work($total);

        if ($downloaded > 0) {
            return Response::json(['finished' => false, 'message' => 'downloaded '.$downloaded]);
        }

        $uploaded = UploadQueue::work($total);

        if ($uploaded > 0) {
            return Response::json(['finished' => false, 'message' => 'uploaded '.$uploaded]);
        }

        $calendars = Client::syncCalendars();

        if ($calendars > 0) {
            return Response::json(['finished' => false, 'message' => $calendars.' calendars must be updated']);
        }

        return Response::json(['finished' => true, 'message' => 'finished sync']);
    }

    /**
     * Compares calendar list from remote and local, adds missing
     * calendars and updates their tasks if necessary.
     */
    private static function syncCalendars(): int
    {
        $i = 0;

        foreach (Remote::all() as $remote) {
            $client = Client::new($remote);

            foreach ($client->calendars() as $calendar) {
                $local = Calendar::query()->where('href', $calendar->href)->first();

                // create calendar if not exists
                if ($local === null) {
                    $calendar->save();
                    $client->updateCalendar($calendar, $calendar->ctag);
                    $i++;

                    continue;
                }

                // update calendar if ctag and so content has changed
                if ($calendar->ctag !== $local->ctag) {

                    $client->updateCalendar($local, $calendar->ctag);
                    $i++;
                }
            }
        }

        return $i;
    }

    /**
     * @return Collection<int, Calendar>
     *
     * @throws ConnectionException|StatusCodeException
     */
    public function calendars(): Collection
    {
        $xmlRequest = '<?xml version="1.0" encoding="utf-8" ?>
        <d:propfind xmlns:d="DAV:"
            xmlns:cs="http://calendarserver.org/ns/"
            xmlns:ical="http://apple.com/ns/ical/">
            <d:prop>
                <d:displayname />
                <cs:getctag />
                <ical:calendar-color />
            </d:prop>
        </d:propfind>';

        $ch = curl_init($this->remote->href);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Depth: 1',
            'Prefer: return-minimal',
            'Content-Type: application/xml; charset=utf-8',
            'Content-Length: '.strlen($xmlRequest),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_USERPWD, $this->remote->username.':'.$this->remote->password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $response = str_replace('<d:multistatus', '<d:multistatus xmlns:x1="http://apple.com/ns/ical/"', $response);

        if (curl_errno($ch)) {
            throw new ConnectionException(curl_error($ch));
        }

        curl_close($ch);

        $xml = simplexml_load_string($response);

        $calendars = collect();

        foreach ($xml->xpath('//d:response') as $response) {
            $status = (string) ($response->xpath('d:propstat/d:status')[0] ?? null);

            if (str_contains($status, '418')) {
                continue;
            }

            if (! str_contains($status, '200')) {
                throw new StatusCodeException($status);
            }

            $href = (string) ($response->xpath('d:href')[0] ?? null);
            $ctag = (string) ($response->xpath('d:propstat/d:prop/cs:getctag')[0] ?? null);
            $name = (string) ($response->xpath('d:propstat/d:prop/d:displayname')[0] ?? null);
            $color = (string) ($response->xpath('d:propstat/d:prop/x1:calendar-color')[0] ?? null);

            $calendars[] = (new Calendar)->fill([
                'remote_id' => $this->remote->id,
                'href' => $href,
                'ctag' => $ctag,
                'name' => $name,
                'color' => $color,
            ]);
        }

        return $calendars;
    }

    public function tasks(Calendar $calendar, array $hrefs): Generator
    {
        $url = trim($calendar->full_href, '/').'/';

        $multi = collect($hrefs)
            ->map(fn (string $href) => '<d:href>'.$href.'</d:href>')
            ->join('');

        $xmlRequest = '<c:calendar-multiget xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
            <d:prop>
                <d:getetag />
                <c:calendar-data />
            </d:prop>
            '.$multi.'
        </c:calendar-multiget>';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'REPORT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Depth: 1',
            'Prefer: return-minimal',
            'Content-Type: application/xml; charset=utf-8',
            'Content-Length: '.strlen($xmlRequest),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_USERPWD, $this->remote->username.':'.$this->remote->password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new ConnectionException(curl_error($ch));
        }

        curl_close($ch);

        $xml = simplexml_load_string($response);

        $xml->registerXPathNamespace('d', 'DAV:');
        $xml->registerXPathNamespace('cal', 'urn:ietf:params:xml:ns:caldav');

        foreach ($xml->xpath('//d:response') as $task) {
            if (! str_contains($status = (string) ($task->xpath('d:propstat/d:status')[0] ?? null), '200')) {
                report(new ConnectionException($status));

                continue;
            }

            $href = (string) ($task->xpath('d:href')[0] ?? null);
            $etag = (string) ($task->xpath('d:propstat/d:prop/d:getetag')[0] ?? null);
            $ical = (string) ($task->xpath('d:propstat/d:prop/cal:calendar-data')[0] ?? null);

            yield (new Task)->fill([
                'calendar_id' => $calendar->id,
                'href' => $href,
                'etag' => $etag,
                'ical' => $ical,
            ]);
        }
    }

    public function updateCalendar(Calendar $calendar, string $ctagOnSuccess): void
    {
        $locals = Task::query()
            ->where('calendar_id', $calendar->id)
            ->get(['href', 'etag'])
            ->keyBy('href');

        $remotes = $this->etags($calendar);

        $diff = 0;

        foreach ($remotes as $href => $_) {
            // create calendar if not exists
            if (! $locals->has($href)) {
                DownloadQueue::add($calendar->id, $href);
                $diff++;

                continue;
            }

            // update calendar if ctag and so content has changed
            if ($locals[$href]->etag !== $remotes[$href]->etag) {
                DownloadQueue::add($calendar->id, $href);
                $diff++;
            }
        }

        // if nothing has changed, apply the ctag
        if ($diff === 0) {
            $calendar->ctag = $ctagOnSuccess;
            $calendar->save();
        }
    }

    /**
     * @throws ConnectionException
     */
    public function updateTask(Task $task): void
    {
        $ch = curl_init($task->full_href);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/calendar; charset=utf-8',
            'If-Match: '.$task->etag,
            'Content-Length: '.strlen($task->ical),
        ]);
        curl_setopt($ch, CURLOPT_USERPWD, $this->remote->username.':'.$this->remote->password);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $task->ical);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new ConnectionException(curl_error($ch));
        }

        if ($response === false) {
            throw new ConnectionException('response = false');
        }

        if (trim($response) !== '') {
            $xml = simplexml_load_string($response);

            $xml->registerXPathNamespace('d', 'DAV:');

            foreach ($xml->xpath('//d:error') as $error) {
                foreach ($error->xpath('//s:exception') as $exception) {
                    if (str_contains('Sabre\DAV\Exception\PreconditionFailed', $exception)) {
                        $this->updateTask($task);

                        return;
                    }
                }

                //                dd($task->ical);

                throw new CalDavException($response);
            }

            curl_close($ch);
        }

        foreach ($this->tasks($task->calendar, hrefs: [$task->href]) as $task) {
            $task->createOrUpdate();
        }
    }

    /**
     * @throws ConnectionException
     */
    private function etags(Calendar $calendar): Collection
    {
        $url = trim($calendar->full_href, '/').'/';

        $xmlRequest = '<c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav">
            <d:prop>
                <d:getetag />
            </d:prop>
            <c:filter>
                <c:comp-filter name="VCALENDAR">
                    <c:comp-filter name="VTODO" />
                </c:comp-filter>
            </c:filter>
        </c:calendar-query>';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'REPORT');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Depth: 1',
            'Prefer: return-minimal',
            'Content-Type: application/xml; charset=utf-8',
            'Content-Length: '.strlen($xmlRequest),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
        curl_setopt($ch, CURLOPT_USERPWD, $this->remote->username.':'.$this->remote->password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new ConnectionException(curl_error($ch));
        }

        curl_close($ch);

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
