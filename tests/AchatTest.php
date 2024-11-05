<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PurchaseTest extends WebTestCase
{
    public function testPurchase()
    {
        $client = static::createClient();

        $client->request('POST', '/checkout', [
            'cart_id' => 1,
            'payment_method' => 'stripe',
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Achat réussi', $client->getResponse()->getContent());
    }
}
