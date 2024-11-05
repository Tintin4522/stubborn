<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CartTest extends WebTestCase
{
    public function testAddProductToCart()
    {
        $client = static::createClient();

        $client->request('POST', '/cart/add', [
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Produit ajouté au panier', $client->getResponse()->getContent());
    }
}
