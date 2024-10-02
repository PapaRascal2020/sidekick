<?php

namespace PapaRascalDev\Sidekick;

use Illuminate\Database\Eloquent\Model;
use PapaRascalDev\Sidekick\Models\SidekickConversation as SidekickConversationModel;


class SidekickConversation
{

    public SidekickDriverInterface $driver;
    public SidekickConversationModel $model;

    public function begin ( SidekickDriverInterface $driver,
                            string  $model,
                            string  $systemPrompt   = '',
                            int     $maxTokens      = 1024 ): SidekickConversation
    {
        $this->model = SidekickConversationModel::create( [
            'model'         => $model,
            'class'         => get_class($driver),
            'system_prompt' => $systemPrompt,
            'max_tokens'    => $maxTokens,
        ] );

        $this->driver = new $driver();

        return $this;
    }

    public function resume ( string $sidekickConversationId ): SidekickConversation
    {
        $this->model = SidekickConversationModel::find( $sidekickConversationId );
        $this->driver = new $this->model->class();

        return $this;
    }

    public function delete ( string $sidekickConversationId ): void
    {
        $conversation = SidekickConversationModel::find( $sidekickConversationId );

        if ( $conversation ) $conversation->delete();
    }

    public function sendMessage ( string $message,
                                  bool $streamed = false ): array | string
    {
        return ( $streamed ) ? $this->getStreamedResponse( $message, $this->getMessages() ) : $this->getResponse( $message, $this->getMessages() );
    }

    public function database(): Model
    {
        return new SidekickConversationModel();
    }

    private function getStreamedResponse ( string $message,
                                           object|array $allMessages ): false|string
    {
        $response =  $this->driver->complete(
            model: $this->model->model,
            systemPrompt: $this->model->system_prompt,
            message: $message,
            allMessages: $allMessages,
            maxTokens: $this->model->max_tokens,
            stream: true
        );

        if(isset($response['error'])) return json_encode($response);

        $this->model->messages()->create([
            'role' => $this->driver->messageRoles['user'],
            'content' => $message
        ]);

        $this->model->messages()->create([
            'role' => $this->driver->messageRoles['assistant'],
            'content' => $response
        ]);

        return $response;
    }

    private function getResponse( string $message,
                                  object|array $allMessages ) : string
    {
        $response = $this->driver->complete(
            model: $this->model->model,
            systemPrompt: $this->model->system_prompt,
            message: $message,
            allMessages: $allMessages,
            maxTokens: $this->model->max_tokens,
            stream: false
        );

        if(isset($response['error'])) return json_encode($response);

        $this->model->messages()->create([
            'role' => $this->driver->messageRoles['user'],
            'content' => $message
        ]);

        $this->model->messages()->create([
            'role' => $this->driver->messageRoles['assistant'],
            'content' => $response
        ]);

        return $response;
    }

    private function getMessages ()
    {
        $mappedMessages = [];
        foreach($this->model->messages() as $message) {
            foreach ($this->driver->chatMaps as $oldKey => $newKey) {
                $message[$newKey] = $message[$oldKey];
                unset($message[$oldKey]);
            }
            $mappedMessages[] = $message;
        }

        if ( $this->driver->listAsObject ) return $mappedMessages;

        return [
            ...$mappedMessages,
        ];
    }
}
