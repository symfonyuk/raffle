<?php

declare(strict_types=1);

namespace App\Command;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class PickWinnerCommand extends Command
{
    protected static $defaultName = 'app:pick-winner';

    private DynamoDbClient $dynamoDbClient;

    private Marshaler $marshaler;

    public function __construct(DynamoDbClient $dynamoDbClient)
    {
        parent::__construct(self::$defaultName);

        $this->dynamoDbClient = $dynamoDbClient;
        $this->marshaler = new Marshaler();
    }

    protected function configure(): void
    {
        $this->addOption('skip-fancy-shuffle', null, InputOption::VALUE_OPTIONAL, 'Makes the process a bit quicker', true);
        $this->addArgument('prize', InputArgument::REQUIRED, 'Which prize?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->dynamoDbClient->query([
            'TableName' => CreateRaffleTableCommand::TABLE_NAME,
            'KeyConditionExpression' => 'date_entered = :dateEntered',
            'ExpressionAttributeValues' => [
                ':dateEntered' => ['S' => (new \DateTimeImmutable())->format('Y-m-d')]
            ]
        ]);

        $entries = \array_reduce($result['Items'], function (array $carry, array $item) {
            $data = $this->marshaler->unmarshalItem($item);

            if (false === isset($data['prizeWon'])) {
                $carry[] = $data;
            }

            return $carry;
        }, []);

        if([] === $entries) {
            throw new \RuntimeException('Whoa, not enough entries to continue!');
        }

        $output->writeln('<info>' . count($entries) . ' entries</info>');
        sleep(1);

        $this->doFancyAnimatedBit($input, $output, $entries);

        $this->pickWinner($input, $output, $entries);

        return Command::SUCCESS;
    }

    private function doFancyAnimatedBit(InputInterface $input, OutputInterface $output, array $entries): void
    {
        $output->writeln('<comment>SHUFFLING Entries</comment>');
        usleep(100);

        shuffle($entries);

        $section = $output->section();

        if (null !== $input->getOption('skip-fancy-shuffle')) {
            $section->writeln($entries[0]['name']);
            foreach ($entries as $entry) {
                usleep(40000);
                $section->overwrite($entry['name']);
            }
        }

        $section->overwrite('<comment>PICKING WINNER</comment>');
        sleep(1);
    }

    private function pickWinner(InputInterface $input, OutputInterface $output, $entries): void
    {
        $winner = $entries[rand(0, count($entries) - 1)];

        $output->writeln('<bg=green>                           </>');
        $output->writeln('<bg=green>  The winner is:           </>');
        $output->writeln('<bg=green;options=bold>  ' . $winner['name'] . str_repeat(' ', 25 - strlen($winner['name'])) . '</>');
        $output->writeln('<bg=green>                           </>');

        $eav = $this->marshaler->marshalItem([
            ':p' => $input->getArgument('prize')
        ]);

        try {
            $this->dynamoDbClient->updateItem([
                'TableName' => CreateRaffleTableCommand::TABLE_NAME,
                'Key' => $this->marshaler->marshalItem([
                    'date_entered' => $winner['date_entered'],
                    'email' => $winner['email']
                ]),
                'UpdateExpression' => 'set prizeWon = :p',
                'ExpressionAttributeValues' => $eav,
            ]);

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        sleep(1);
    }
}