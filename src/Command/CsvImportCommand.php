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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting import of Feed...');

        $reader = Reader::createFromPath('%kernel.project_dir%/../data/try10p.csv')
            ->setHeaderOffset(0);

        foreach($reader as $row){
            $participant = new Participant();
            $participant
                ->setEmail($row['email'])
                ->setRoles(["ROLE_USER"])
                //->setPassword($userPasswordHasher->hashPassword($participant,$row['password']))
                ->setPassword($row['password'])
                ->setNom($row['nom'])
                ->setPrenom($row['prenom'])
                ->setTelephone($row['telephone'])
                ->setAdministrateur(0)
                ->setActif(1)
                ->setPseudo($row['pseudo']);


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
            $this->em->persist($participant);
        }
        $this->em->flush();

        $io->success('Command exited cleanly!');
        return 1;
    }

}