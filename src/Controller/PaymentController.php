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
        $amount = 1000; // Montant fixe de 10 € en centimes
    
        if ($amount <= 0) {
            throw new \Exception('Le montant doit être supérieur à zéro.');
        }
    
        $paymentIntent = $this->stripeService->createPaymentIntent($amount);
    
        // Logique de réussite du paiement
        if ($paymentIntent->status === 'succeeded') {
    
            // Formatage du montant pour l'affichage
            $formattedAmount = $amount / 100; // Convertir les centimes en euros
    
            return new JsonResponse([
                'clientSecret' => $paymentIntent->client_secret,
                'amount' => $formattedAmount, // Montant dans la réponse
                'message' => 'Paiement réussi de ' . $formattedAmount . ' €', // Message de succès
                'redirectUrl' => $this->generateUrl('cart_show', [], UrlGeneratorInterface::ABSOLUTE_URL) // URL de redirection
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
