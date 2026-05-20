<?php

namespace App\Services\AiChat;

use App\Services\ReportsService;

class  FetchRepportsService {
        /**
     * fetch repports to provide data to chatbot
     */

    public function fetchrepportData(array $intents)
    {


        $service = app(ReportsService::class);
        $data = [];

        foreach ($intents as $intent) {
            if (method_exists($service, $intent)) {
                $data[$intent] =  $service->$intent();
            }
        }

        return $data;
    }
}
