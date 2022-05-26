<?php

namespace App\Controller;

use App\Entity\Order;
use App\Manager\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\MailerService;

class OrderSuccessController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index($stripeSessionId, Cart $cart, MailerService $mailer): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if(!$order->getIsPaid()) {
            //vider la session de "cart"
            $cart->remove();

            // Modifier le statut isPaid de notre commande en mettant 1
            $order->setIsPaid(1);
            $this->entityManager->flush();

            $content = "Bonjour " . $order->getUser()->getFirstname()."<br/> Merci pour votre commande." ;
            $mailer->sendEmail($order->getUser()->getEmail(),'Votre commande est bien validée',$content);
        }

        return $this->render('order_success/index.html.twig', [
            'order' => $order,
        ]);
    }
}
