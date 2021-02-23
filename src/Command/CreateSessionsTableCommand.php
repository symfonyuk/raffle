<?php

declare(strict_types=1);

namespace App\Command;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateSessionsTableCommand extends Command
{
    protected static $defaultName = 'dev:create-sessions-table';

    private DynamoDbClient $dynamoDbClient;

    private const TABLE_NAME = 'symfony-uk-raffle-sessions';

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
                    'AttributeName' => 'id',
                    'KeyType' => 'HASH'  //Partition key
                ]
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'id',
                    'AttributeType' => 'S'
                ]
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