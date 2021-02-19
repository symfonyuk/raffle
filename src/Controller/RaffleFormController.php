<?php

declare(strict_types=1);

namespace App\Controller;

use App\Command\CreateRaffleTableCommand;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
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

    private DynamoDbClient $dynamoDbClient;

    public function __construct(FormFactoryInterface $formFactory, Environment $twig, DynamoDbClient $dynamoDbClient)
    {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->dynamoDbClient = $dynamoDbClient;
    }

    public function form(Request $request): Response
    {
        $form = $this->formFactory->createBuilder()
            ->add('name', TextType::class)
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        $marshaler = new Marshaler();

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $data['date_entered'] = (new \DateTimeImmutable())->format('Y-m-d');

            $this->dynamoDbClient->putItem([
                'TableName' => CreateRaffleTableCommand::TABLE_NAME,
                'Item' => $marshaler->marshalItem($data)
            ]);

            return new Response(
                $this->twig->render('form-submitted.html.twig', [
                    'form' => $form->createView(),
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