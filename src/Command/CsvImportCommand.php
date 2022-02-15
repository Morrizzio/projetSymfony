<?php

namespace App\Command;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


/**
 * Class CsvImportCommand
 * @package AppBundle\ConsoleCommand
 */
class CsvImportCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * CsvImportCommand constructor
     * @param EntityManagerInterface $em
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }


    /**
     * configure
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('app:csv:import')
            ->setDescription('Imports the mock CSV data file');
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting import of Feed...');

        $reader = Reader::createFromPath('%kernel.project_dir%/../data/datas.csv')
            ->setHeaderOffset(0);


        foreach($reader as $row){
            $participant = new Participant();

            if($row['campus'] == null)
                dd($participant);

            $participant
                ->setEmail($row['email'])
                ->setNom($row['nom'])
                ->setPrenom($row['prenom'])
                ->setPseudo($row['pseudo'])
                ->setTelephone($row['telephone'])

                ->setRoles(["ROLE_USER"])

//                ->setPassword($userPasswordHasher->hashPassword($participant,'Az123@'))
                ->setAdministrateur(0)
                ->setActif(1);

            /*
            $campus = $this->em->getRepository('App:Campus')
                ->findOneBy([
                    'nom' => $row['campus']
                ]);

            if($campus === null){
                $campus = (new Campus())
                    ->setNom($row['campus']);
                $this->em->persist($campus);
                $this->em->flush();
            }
            $participant->setCampus($campus);
            */
            $this->em->persist($participant);
        }
        $this->em->flush();

        $io->success('Command exited cleanly!');
        return 1;
    }

}