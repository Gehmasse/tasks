<?php

namespace App;

use App\Exceptions\ConnectionException;
use App\Models\Calendar;
use App\Models\Remote;
use App\Models\Task;
use Generator;
use Illuminate\Support\Collection;

readonly class Client
{
    private function __construct(private Remote $remote) {}

    public static function new(Remote $remote): Client
    {
        return new Client($remote);
    }

    public static function sync(): void
    {
        Remote::all()->each(function (Remote $remote) {
            $locals = $remote->calendars->keyBy('href');

            try {
                $remotes = Client::new($remote)->calendars()->keyBy('href');
            } catch (ConnectionException $e) {
                report($e);

                return;
            }

            foreach ($remotes as $href => $_) {
                // create calendar if not exists
                if (! $locals->has($href)) {
                    $remotes[$href]->save();

                    try {
                        Client::new($remote)->updateCalendar($remotes[$href]);
                        // FIXME: update ctag here
                    } catch (ConnectionException $e) {
                        report($e);

                        continue;
                    }

                    continue;
                }

                // update calendar if ctag and so content has changed
                if ($locals[$href]->ctag !== $remotes[$href]->ctag) {
                    try {
                        Client::new($remote)->updateCalendar($locals[$href]);
                        // FIXME: update ctag here
                    } catch (ConnectionException $e) {
                        report($e);

                        continue;
                    }
                }
            }
        });
    }

    /**
     * @return Collection<int, Calendar>
     *
     * @throws ConnectionException
     */
    public function calendars(): Collection
    {
        $xmlRequest = '<?xml version="1.0" encoding="utf-8" ?>
        <d:propfind xmlns:d="DAV:" xmlns:cs="http://calendarserver.org/ns/" xmlns:ical="http://apple.com/ns/ical/">
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

        if (curl_errno($ch)) {
            throw new ConnectionException(curl_error($ch));
        }

        curl_close($ch);

        $xml = simplexml_load_string($response);

        $xml->registerXPathNamespace('d', 'DAV:');
        $xml->registerXPathNamespace('cs', 'http://calendarserver.org/ns/');
        $xml->registerXPathNamespace('ical', 'http://apple.com/ns/ical/');

        $calendars = collect();

        foreach ($xml->xpath('//d:response') as $response) {
            if (! str_contains($status = (string) ($response->xpath('d:propstat/d:status')[0] ?? null), '200')) {
                report(new ConnectionException($status));

                continue;
            }

            $href = (string) ($response->xpath('d:href')[0] ?? null);
            $ctag = (string) ($response->xpath('d:propstat/d:prop/cs:getctag')[0] ?? null);
            $name = (string) ($response->xpath('d:propstat/d:prop/d:displayname')[0] ?? null);
            $color = ''; // (string)($response->xpath('.//ical:calendar-color')[0] ?? null); FIXME

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

    private function tasks(Calendar $calendar, array $hrefs): Generator
    {
        $url = trim($calendar->full_href, '/').'/';

        $multi = collect($hrefs)->map(fn (string $href) => '<d:href>'.$href.'</d:href>')->join('');
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

    /**
     * @throws ConnectionException
     */
    public function updateCalendar(Calendar $calendar): void
    {
        $locals = Task::query()
            ->where('calendar_id', $calendar->id)
            ->get(['href', 'etag'])
            ->keyBy('href');

        $remotes = $this->etags($calendar);

        $changed = [];

        foreach ($remotes as $href => $_) {
            // create calendar if not exists
            if (! $locals->has($href)) {
                $changed[] = $href;

                continue;
            }

            // update calendar if ctag and so content has changed
            if ($locals[$href]->etag !== $remotes[$href]->etag) {
                $changed[] = $href;
            }
        }

        foreach ($this->tasks($calendar, hrefs: $changed) as $task) {
            $task->storeOrUpdate();
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $task->ical);      // Attach the data to send

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new ConnectionException(curl_error($ch));
        } else {
            echo 'Response: '.$response;
        }

        curl_close($ch);

        foreach ($this->tasks($task->calendar, hrefs: [$task->href]) as $task) {
            $task->storeOrUpdate();
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
