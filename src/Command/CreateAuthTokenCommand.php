<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Main\AuthToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-auth-token',
    description: 'Erstellt einen neuen API-AuthToken',
)]
class CreateAuthTokenCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('description', InputArgument::OPTIONAL, 'Beschreibung für das Token')
            ->addArgument('expiresAt', InputArgument::OPTIONAL, 'Ablaufdatum (z.B. "+1 year" oder "2025-12-31")');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tokenValue = bin2hex(random_bytes(32));
        $authToken = new AuthToken();
        $authToken->setToken($tokenValue);

        if ($desc = $input->getArgument('description')) {
            $authToken->setDescription($desc);
        }

        if ($exp = $input->getArgument('expiresAt')) {
            $authToken->setExpiresAt(new \DateTime($exp));
        }

        $this->em->persist($authToken);
        $this->em->flush();

        $output->writeln('Neues Token: ' . $tokenValue);

        return Command::SUCCESS;
    }
}
