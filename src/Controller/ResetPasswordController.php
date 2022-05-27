<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request, MailerService $mailer): Response
    {
        if($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if($request->get('email')) {
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            if($user) {
                // 1 : Enregistrer en bdd la demande de reset_password avec user, token, createdAt
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTime());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                // 2 : Envoyer un email à l'utilisateur avec un lien lui permettant de maj son mdp
                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ]);

                $content = "Bonjour ".$user->getFirstname(). ",<br/> Vous avez demandé à réinitialiser votre mot de passe sur le site La Boutique.<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'> mettre à jour votre mot de passe </a>.";

                $mailer->sendEmail($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), $content, 'test');

                $this->addFlash('notice', "Vous allez recevoir dans quelques instant un mail afin de réinitialiser votre mot de passe.");

            } else {
                $this->addFlash('notice', "Cette adresse email est inconnu");
            }
        }

        return $this->render('reset_password/index.html.twig');
    }
    
    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update(Request $request, $token, UserPasswordHasherInterface $encoder) {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);
        
        if(!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }

        // Verifier si le createdAt = now - 3h
        $now = new \DateTime();
        if($now > $reset_password->getCreatedAt()->modify('+ 3 hour')) {
            // Modifier mon mdp
            $this->addFlash('notice', "Votre demande de mot de passe a expiré. Merci de le renouveller");
            return $this->redirectToRoute('reset_password');
        }

        // Rendre une vue avec mdp et confirmer votre mdp
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $new_pwd = $form->get('new_password')->getData();

            // Encoder mot de passe
            $password = $encoder->hashPassword($reset_password->getUser(), $new_pwd);
            $reset_password->getUser()->setPassword($password);

            // Flush en bdd
            $this->entityManager->flush();

            // Redirection de l'utilisateur vers la page de connexion
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
