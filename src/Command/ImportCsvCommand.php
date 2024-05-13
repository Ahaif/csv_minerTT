<?php

namespace App\Command;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use League\Csv\Reader;

class ImportCsvCommand extends Command
{
    protected static $defaultName = 'app:import-csv';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct('app:import-csv');
        
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Imports a CSV file into the database from a specified directory.')
             ->addArgument('directory', InputArgument::REQUIRED, 'The directory to search for CSV files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument('directory');

        $finder = new Finder();
        $finder->in($directory)->files()->name('*.csv');

        if (!$finder->hasResults()) {
            $io->error('No CSV files found in the specified directory.');
            return Command::FAILURE;
        }

        foreach ($finder as $file) {
            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0); // Assume first row as CSV header
            $records = $csv->getRecords(['reference', 'designation', 'quantities', 'price']);

            foreach ($records as $record) {
                $article = $this->entityManager->getRepository(Article::class)->findOneBy(['reference' => $record['reference']]);

                if ($article) {
                    // Update existing article
                    $article->setDesignation($record['designation']);
                    $article->setQuantities((int) $record['quantities']);
                    $article->setPrice((float) $record['price']);
                } else {
                    // Create new article
                    $article = new Article();
                    $article->setReference($record['reference']);
                    $article->setDesignation($record['designation']);
                    $article->setQuantities((int) $record['quantities']);
                    $article->setPrice((float) $record['price']);
                    $this->entityManager->persist($article);
                }
            }

            $this->entityManager->flush(); // Commit changes to the database
        }

        $io->success('CSV import successful.');
        return Command::SUCCESS;
    }
}

?>