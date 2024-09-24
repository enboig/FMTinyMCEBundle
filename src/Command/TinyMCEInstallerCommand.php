<?php

namespace FM\TinyMCEBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Style\SymfonyStyle;

class TinyMCEInstallerCommand extends Command
{
    protected static $defaultName = 'fm:tinymce:install';
    private $projectDir;

    public function __construct(string $projectDir)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
    }

    protected function configure()
    {
        $this
            ->setDescription('Instal·la TinyMCE al directori públic')
            ->addOption('target', null, InputOption::VALUE_OPTIONAL, 'Directori on instal·lar TinyMCE', 'public/bundles/fmtinymce/tinymce');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        $targetDir = $this->projectDir . '/' . $input->getOption('target');

        // Comprovar si el directori existeix
        if (!$filesystem->exists($targetDir)) {
            $filesystem->mkdir($targetDir, 0777);
            $io->success('Directori creat: ' . $targetDir);
        }

        // Copiar els fitxers de TinyMCE (actualitza aquest camí amb el correcte)
        $tinymceSourcePath = __DIR__ . '/../../Resources/public/tinymce'; // Aquí és on tens TinyMCE
        if (!$filesystem->exists($tinymceSourcePath)) {
            $io->error('El directori de TinyMCE no existeix: ' . $tinymceSourcePath);
            return Command::FAILURE;
        }

        $filesystem->mirror($tinymceSourcePath, $targetDir);
        $io->success('TinyMCE s\'ha instal·lat correctament a: ' . $targetDir);

        return Command::SUCCESS;
    }
}
