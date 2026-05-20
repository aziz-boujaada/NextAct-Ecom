<?php

namespace App\Services\AiChat;

class   DetectIntentsService
{
    /**
     * detect subject or intent from user message 
     * 
     */
    public function detectIntents(string $message): array
    {
        $message = strtolower($message);

        $intents = [];

       
          if (
            str_contains($message, 'sales') ||
            str_contains($message, 'sale') ||
            str_contains($message, 'vents') ||
            str_contains($message, 'refundes') 
        ) {
            $intents[] = 'sales';
        }

        if (
            str_contains($message, 'financial') ||
            str_contains($message, 'finance') ||
            str_contains($message, 'refundes')
        ) {
            $intents[] = 'financial';
        }

        if (
            str_contains($message, 'inventory') ||
            str_contains($message, 'stock') ||
            str_contains($message, 'products') ||
            str_contains($message, 'product')
        ) {
            $intents[] = 'inventory';
        }

        if (
            str_contains($message, 'devis') ||
            str_contains($message, 'quote')
        ) {
            $intents[] = 'devis';
        }

        if (
            str_contains($message, 'purchase') ||
            str_contains($message, 'purchasing')
        ) {
            $intents[] = 'purchasing';
        }


        $intents = array_unique($intents);


        if (empty($intents)) {
            return ['sales'];
        }

        return array_values($intents);
    }
}
