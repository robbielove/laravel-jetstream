<?php

namespace App\Services;

use Discord\Discord;
use Discord\Parts\Channel\Message;

class DiscordService
{
    protected $discord;
    protected $chatGPTService;

    public function __construct(ChatGPTService $chatGPTService)
    {
        $this->discord = new Discord([
            'token' => env('DISCORD_BOT_TOKEN'),
        ]);

        $this->chatGPTService = $chatGPTService;
    }

    public function listen()
    {
        $this->discord->on('ready', function (Discord $discord) {
            echo "Bot is ready!", PHP_EOL;

            $discord->on('message', function (Message $message) {
                if (str_contains($message->content, '@gpt')) {
                    $this->handleGPTMessage($message);
                }
            });
        });

        $this->discord->run();
    }

    private function handleGPTMessage(Message $message)
    {
        $inputMessage = str_replace('@gpt', '', $message->content);
        $response = $this->chatGPTService->sendMessage($inputMessage);
        $outputMessage = $response['choices'][0]['message']['content'];

        $message->channel->sendMessage($outputMessage);
    }
}
