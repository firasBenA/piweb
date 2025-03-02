<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private string $twilioSid;
    private string $twilioToken;
    private string $twilioPhoneNumber;

    public function __construct(string $twilioSid, string $twilioToken, string $twilioPhoneNumber)
    {
        $this->twilioSid = $twilioSid;
        $this->twilioToken = $twilioToken;
        $this->twilioPhoneNumber = $twilioPhoneNumber;
    }

    public function sendSms(string $to, string $message): void
    {
        $client = new Client($this->twilioSid, $this->twilioToken);

        $client->messages->create(
            $to,
            [
                'from' => $this->twilioPhoneNumber,
                'body' => $message,
            ]
        );
    }
}
