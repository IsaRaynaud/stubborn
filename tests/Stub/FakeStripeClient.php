<?php
namespace App\Tests\Stub;

use Stripe\StripeClient;

class FakeStripeClient extends StripeClient
{
    public function __construct()
    {
        parent::__construct('sk_test_fake');

        $this->checkout = new class {
            public $sessions;
            public function __construct()
            {
                $this->sessions = new class {
                    public function create(array $payload)
                    {
                        return new class {
                            public string $id = 'cs_test_123';
                            private string $url = 'https://stripe.test/checkout/cs_test_123';
                            public function toArray(): array { return ['id' => $this->id, 'url' => $this->url]; }
                            public function __get(string $name) { return $name === 'url' ? $this->url : null; }
                        };
                    }
                };
            }
        };
    }
}