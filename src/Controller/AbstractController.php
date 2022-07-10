<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends SymfonyAbstractController
{
    protected function redirectSeeOther(string $route, array $params = []): RedirectResponse
    {
        return $this->redirectToRoute($route, $params, Response::HTTP_SEE_OTHER);
    }

    protected function getResponseBasedOnFormValidationStatus(FormInterface $form, ?Response $response = null): Response
    {
        if ($response === null) {
            $response = new Response();
        }

        if ($response->getStatusCode() === Response::HTTP_OK && $form->isSubmitted() && ! $form->isValid()) {
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $response;
    }

    protected function flashFormErrors(FormInterface $form): void
    {
        $errors = $this->getFormErrors($form);
        $errors = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($errors)));
        $this->addFlash(
            type: 'error',
            message: implode(separator: '\n', array: $errors)
        );
    }

    protected function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                $childErrors = $this->getFormErrors($childForm);
                if ($childErrors) {
                    $errors[] = $childErrors;
                }
            }
        }

        return $errors;
    }

    protected function addSomethingWentWrongFlash(): void
    {
        $this->addFlash('error', 'Désolé, une erreur est survenu !');
    }
}
