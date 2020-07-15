<?php

namespace App\Command;

use App\Helper\ImportCommandTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class OdsStaticDataMigrate extends Command
{
    use  ImportCommandTrait;

    protected static $defaultName = 'ods:bootstrap:all-static-data';

    private $manager;

    public function __construct(EntityManagerInterface $manager, KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Bootstrap with all static data')
            ->setHelp('Bootstrap with all static data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $odsStaticDataMigrateCommandNames = [
            'ods:import:typespecies',
            'ods:import:species',
            'ods:import:events',
        ];

        foreach ($odsStaticDataMigrateCommandNames as $commandName) {
            switch ($commandName) {
                case 'ods:import:species':
                    $input = new ArrayInput(['importEventSpeciesAndPeriods' => false]);
                    break;
                case 'ods:import:event':
                    $input = new ArrayInput(['importEventSpeciesAndPeriods' => true]);
                    break;
                default:
                    $input = new ArrayInput([]);
                    break;
            }

            $returnCode = $this->runCommand( // from importCommandTrait
                $commandName,
                $input,
                $output
            );
            if (0 !== $returnCode) {
                $output->writeln(sprintf("<error>\n  Something went wrong with \"%s\"\n</error>", $commandName));

                return 1;
            }

            $output->writeln(sprintf("<info>\n  ...\"%s\" done.\n</info>", $commandName));
        }

        $output->writeln("\n...OdsStaticDataMigrate ...Done.\n");

        return 0;
    }
}
