<?php

namespace App\Console\Commands;

use App\MintService;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use React\EventLoop\Loop;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramHandler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:handler';

    protected int $update_id = 0;

    protected MintService $mintService;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private function processingMessage(): void
    {
        try {
            $update_id = $this->update_id;

            $params = ['timeout' => 30];
            if ($update_id > 0) {
                $params['offset'] = $update_id + 1;
            }
            $response = Telegram::getUpdates($params);

            foreach ($response as $update) {
                if($update->isType('message') && $update->message->hasCommand()) {
                    $message = $update->message;
                    $command = $message->entities[0];

                    $text = Str::substr($message->text, $command->offset, $command->length);

                    if($text === '/timer' || $text === '/timer@wild_mint_notificator_bot') {
                        Telegram::sendMessage([
                            'chat_id' => $message->chat->id,
                            'text' => $this->mintService->getText()
                        ]);

                        Notification::firstOrCreate([
                            'chat_id' => $message->chat->id
                        ]);
                    }

                    $this->update_id = $update->updateId;
                }
            }
        } catch (\Exception $e) {
            report($e);
            throw $e;
        }
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $loop = Loop::get();

        $this->mintService = app(MintService::class);

        $this->info('Telegram handler starting...');

        $loop?->addPeriodicTimer(1, function () {
            $this->processingMessage();
        });

        $this->registerSignals($loop);
        $loop?->run();
    }

    private function registerSignals($loop): void
    {
        if(extension_loaded('pcntl')) {
            pcntl_async_signals(true);

            $func = function () use ($loop) {
                $this->info("\nShutdown...");
                $loop->stop();
                exit;
            };

            pcntl_signal(SIGTERM, $func);
            pcntl_signal(SIGINT, $func);
        }
    }
}
