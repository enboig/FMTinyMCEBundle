<?php

namespace FM\TinyMCEBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class TinyMCEInstallerCommand extends Command
{
    protected static $defaultName = 'fm:tinymce:install';

    private $projectDir;
    private $tinymceUrl = 'https://download.tiny.cloud/tinymce/community/tinymce_7.3.0.zip';

    public function __construct(string $projectDir)
    {
        parent::__construct();
        $this->projectDir = $projectDir;
    }

    protected function configure()
    {
        $this
            ->setDescription('Instal·la TinyMCE descarregant i descomprimint l\'última versió')
            ->addOption('target', null, InputOption::VALUE_OPTIONAL, 'Directori on instal·lar TinyMCE', 'public/bundles/fmtinymce/tinymce');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        // Obtenim el directori on es copiaran els fitxers
        $targetDir = $this->projectDir . '/' . $input->getOption('target');

        // Comprovar si el directori existeix
        if (!$filesystem->exists($targetDir)) {
            $filesystem->mkdir($targetDir, 0777);
            $io->success('Directori creat: ' . $targetDir);
        }

        // Directori temporal per descomprimir el zip
        $tempDir = sys_get_temp_dir() . '/tinymce';

        if ($filesystem->exists($tempDir)) {
            $filesystem->remove($tempDir); // Esborrem el directori temporal si existeix
        }

        // Descarregar el fitxer ZIP
        $zipPath = $tempDir . '/tinymce.zip';
        $this->downloadFile($this->tinymceUrl, $zipPath, $io);

        // Descomprimir el fitxer ZIP
        $this->extractZip($zipPath, $tempDir, $io);

        // Copiar el contingut descomprimt a la carpeta de destinació
        $tinymceSourcePath = $tempDir . '/tinymce'; // Aquest és el directori descomprimt
        if (!$filesystem->exists($tinymceSourcePath)) {
            $io->error('No s\'ha trobat el directori descomprimt de TinyMCE a: ' . $tinymceSourcePath);
            return Command::FAILURE;
        }

        // Copiar els fitxers de TinyMCE a la carpeta de destinació
        $filesystem->mirror($tinymceSourcePath, $targetDir);
        $io->success('TinyMCE s\'ha instal·lat correctament a: ' . $targetDir);

        // Netejar el directori temporal
        $filesystem->remove($tempDir);

        return Command::SUCCESS;
    }

    private function downloadFile(string $url, string $path, SymfonyStyle $io)
    {
        $io->info('Descarregant TinyMCE des de ' . $url);

        // Descarregar el fitxer ZIP des de l'URL proporcionada
        $fileContent = file_get_contents($url);

        if ($fileContent === false) {
            $io->error('No s\'ha pogut descarregar el fitxer des de ' . $url);
            return Command::FAILURE;
        }

        // Guardar el contingut descarregat al fitxer ZIP local
        file_put_contents($path, $fileContent);
        $io->success('Descarregat correctament a: ' . $path);
    }

    private function extractZip(string $zipPath, string $destination, SymfonyStyle $io)
    {
        $io->info('Descomprimint el fitxer ZIP...');

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($destination);
            $zip->close();
            $io->success('Fitxer descomprimt correctament a: ' . $destination);
        } else {
            $io->error('No s\'ha pogut descomprimir el fitxer ZIP');
            return Command::FAILURE;
        }
    }
}
