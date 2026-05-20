<?php

namespace App\Services\AiChat;

class ChatService
{

    public function __construct(
        private FetchRepportsService $fetchRepportsService,
        private DetectIntentsService $detectIntentsService,
    ) {}
    public function Aichat(string $message)
    {

        $intents = $this->detectIntentsService->detectIntents($message);

        $data = $this->fetchRepportsService->fetchrepportData($intents);

        if (!$data) {
            return response()->json([
                'answer' => "I don't have enough data for this request."
            ]);
        }

        return $data;
    }
}
