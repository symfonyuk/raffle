<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class RaffleFormController
{
    private FormFactoryInterface $formFactory;

    private Environment $twig;

    public function __construct(FormFactoryInterface $formFactory, Environment $twig)
    {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
    }

    public function form(Request $request): Response
    {
        $form = $this->formFactory->createBuilder()
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email"
            $data = $form->getData();

            return new Response(
                $this->twig->render('form-submitted.html.twig', [
                    'form' => $form->createView()
                ])
            );
        }

        return new Response(
            $this->twig->render('form.html.twig', [
                'form' => $form->createView()
            ])
        );
    }
}