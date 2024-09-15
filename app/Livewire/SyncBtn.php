<?php

namespace App\Livewire;

use App\Client;
use App\Exceptions\ConnectionException;
use Livewire\Component;

class SyncBtn extends Component
{
    use Toastable;

    public function sync(): void
    {
        $this->toast('Syncing...', 'yellow');

        while (true) {
            try {
                $res = Client::syncNextPart();
            } catch (ConnectionException) {
                $this->toast('Sync failed', 'red');

                return;
            }

            $res = json_decode($res->content());

            if ($res->finished) {
                $this->toast('Sync finished. Please reload the page.', 'green');

                return;
            }

            $this->toast($res->message, 'yellow');
        }
    }
}
