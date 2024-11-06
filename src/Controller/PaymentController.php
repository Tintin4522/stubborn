<?php

namespace App\Controller;

use App\Service\StripeService;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    private $stripeService;
    private CartService $cartService;

    public function __construct(StripeService $stripeService, CartService $cartService)
    {
        $this->stripeService = $stripeService;
        $this->cartService = $cartService;
    }

    #[Route('/create-payment-intent', name: 'create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent()
    {
        $amount = 1000; 
    
        if ($amount <= 0) {
            throw new \Exception('Le montant doit être supérieur à zéro.');
        }
    
        $paymentIntent = $this->stripeService->createPaymentIntent($amount);
    
        if ($paymentIntent->status === 'succeeded') {
    
            $formattedAmount = $amount / 100; 
    
            return new JsonResponse([
                'clientSecret' => $paymentIntent->client_secret,
                'amount' => $formattedAmount, 
                'message' => 'Paiement réussi de ' . $formattedAmount . ' €', 
                'redirectUrl' => $this->generateUrl('cart_show', [], UrlGeneratorInterface::ABSOLUTE_URL) 
            ]);
        }
    
        return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);
    }
    

    #[Route('/payment', name: 'payment_page')]
    public function paymentPage()
    {
        return $this->render('payment/payment.html.twig');
    }

}
