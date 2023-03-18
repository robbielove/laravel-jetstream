<?php

namespace App\Jobs;

use App\Services\ChatGPTService;
use App\Services\DiscordService;
use Discord\Discord;
use Discord\Parts\Channel\Message;
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
    private $chatGPTService;

    public function __construct()
    {
        $this->chatGPTService = new ChatGPTService();
    }

    public function handle()
    {
        $token = env('DISCORD_BOT_TOKEN');

        // Add the necessary intents
        $intents = Intents::GUILD_MESSAGES;
        $discord = new Discord(['token' => $token, 'intents' => $intents]);

        $discord->on('ready', function ($discord) {
            //send a message to the channel
//            $discord->getChannel('1086557788899135568')->sendMessage('GPT is ready!');

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
                $response = $this->handleGPTMessage($message);
                $message->channel->sendMessage($response);
            }
        });


        $discord->run();
    }

    private function handleGPTMessage(Message $message)
    {
        $message = json_encode($message);
        $response = $this->chatGPTService->sendMessage($message);
//        dd($response);
        // if reponse choices is empty, return an error message
        if (empty($response['choices'])) {
            // return $response as a discord code block
            return '```' . $response . '```';
        }
        return $response['choices'][0]['message']['content'];
    }

    function transformDiscordMessages(array $discordMessages): array
    {
        $transformedMessages = [];

        foreach ($discordMessages as $discordMessage) {
            $role = $discordMessage->author->bot ? 'assistant' : 'user';
            $content = $discordMessage->content;

            $transformedMessage = [
                'role' => $role,
                'content' => $content,
            ];

            $transformedMessages[] = $transformedMessage;
        }

        return $transformedMessages;
    }
}
