<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\PasteRepository;

class DeleteExpiredPastesCommand extends Command {
    /**
     * @var string
     */
    protected static $defaultName = 'app:delete-expired-pastes';

    /**
     * @var PasteRepository
     */
    private $pasteRepository;

    /**
     * DeleteExpiredPastesCommand constructor.
     * @param PasteRepository $pasteRepository
     */
    public function __construct(PasteRepository $pasteRepository)
    {
        $this->pasteRepository = $pasteRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>START</info>');

        $expiredPastes = $this->pasteRepository->getExpiredPastes();
        $output->writeln('<comment>Expired pastes count '.count($expiredPastes).'</comment>');
        $this->pasteRepository->deleteMultiple($expiredPastes);
        $output->writeln('<comment>All expired pastes have been deleted.</comment>');

        $output->writeln('<info>STOP</info>');

        return 0;
    }

    protected function configure(): void
    {
        $this->setDescription('Deletes expired pastes.');
    }
}