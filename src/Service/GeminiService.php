<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class GeminiService
{
    private $httpClient;
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $params->get('app.gemini_api_key');
    }

    public function sendMessage(string $message)
    {
        try {
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $this->apiKey,
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $message,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return ['error' => 'Erreur de l\'API Gemini : ' . $statusCode];
            }

            $content = $response->toArray();
            if (isset($content['candidates'][0]['content']['parts'][0]['text'])) {
                return ['response' => $content['candidates'][0]['content']['parts'][0]['text']];
            } else {
                return ['error' => 'Réponse inattendue de l\'API Gemini'];
            }
        } catch (\Exception $e) {
            error_log("Gemini API Error: " . $e->getMessage());
            return ['error' => "Une erreur est survenue lors de l'appel à l'API Gemini : " . $e->getMessage()];
        }
    }
}