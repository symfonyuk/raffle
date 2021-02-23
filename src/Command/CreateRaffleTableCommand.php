<?php

declare(strict_types=1);

namespace App\Command;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateRaffleTableCommand extends Command
{
    protected static $defaultName = 'dev:create-raffle-table';

    private DynamoDbClient $dynamoDbClient;

    public const TABLE_NAME = 'SymfonyUkRaffleEntries';

    public function __construct(DynamoDbClient $dynamoDbClient)
    {
        parent::__construct(self::$defaultName);

        $this->dynamoDbClient = $dynamoDbClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $params = [
            'TableName' => self::TABLE_NAME,
            'KeySchema' => [
                [
                    'AttributeName' => 'date_entered',
                    'KeyType' => 'HASH'  //Partition key
                ],
                [
                    'AttributeName' => 'email',
                    'KeyType' => 'RANGE'  //Sort key
                ]
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'date_entered',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'email',
                    'AttributeType' => 'S'
                ],

            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 5,
                'WriteCapacityUnits' => 5
            ]
        ];

        try {
            $result = $this->dynamoDbClient->createTable($params);
            echo 'Created table.  Status: ' .
                $result['TableDescription']['TableStatus'] . "\n";

        } catch (DynamoDbException $e) {
            echo "Unable to create table:\n";
            echo $e->getMessage() . "\n";
        }

        return Command::SUCCESS;
    }
}