<?php

namespace App\Console\Commands;

use App\MintService;
use App\Models\Notification;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendMintNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mint:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $app = app(MintService::class);

        if($app->needSendNotification()) {
            Notification::chunk(100, function ($notifications) use ($app) {
                /** @var Notification $notification */
                foreach ($notifications as $notification) {
                    Telegram::sendMessage([
                        'chat_id' => $notification->chat_id,
                        'text' => $app->getText()
                    ]);
                }
            });
        }
    }
}
