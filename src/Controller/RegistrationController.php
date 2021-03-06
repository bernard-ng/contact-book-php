<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user
                ->setPassword($userPasswordHasher->hashPassword(
                    user: $user,
                    plainPassword: strval($form->get('plainPassword')->getData())
                ))
                ->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            /** @var Response $response */
            $response = $userAuthenticator->authenticateUser($user, $authenticator, $request);

            return $response;
        }

        return $this->render(
            view: 'domain/authentication/register.html.twig',
            parameters: [
                'form' => $form->createView(),
            ],
            response: $this->getResponseBasedOnFormValidationStatus($form)
        );
    }
}
