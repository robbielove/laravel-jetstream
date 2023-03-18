<?php

namespace App\Jobs;

use App\Services\DiscordService;
use Discord\Discord;
use Discord\Parts\User\Member;
use Discord\WebSockets\Intents;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DiscordBotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = null;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        $token = env('DISCORD_BOT_TOKEN');

        // Add the necessary intents
        $intents = Intents::GUILD_MESSAGES;
        $discord = new Discord(['token' => $token, 'intents' => $intents]);

        $discord->on('ready', function ($discord) {
            echo "Bot is ready!", PHP_EOL;
        });

        $discord->on('MESSAGE_CREATE', function ($message) use ($discord) {
            $botUser = $discord->user;
            $mentioned = false;

            // Check if the bot was mentioned
            foreach ($message->mentions as $mention) {
                if ($mention->id === $botUser->id) {
                    $mentioned = true;
                    break;
                }
            }

            // Log the message content and whether the bot was mentioned
            echo 'Message received: ' . $message->content . PHP_EOL;
            echo 'Bot mentioned: ' . ($mentioned ? 'Yes' : 'No') . PHP_EOL;

            // Respond to the mention
            if ($mentioned) {
                $response = 'Hello, I am GPT bot!'; // customize your response here
                $message->channel->sendMessage($response);
            }
        });


        $discord->run();
    }
}
