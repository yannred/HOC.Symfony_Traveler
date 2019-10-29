<?php

namespace App\DataFixtures;

use App\Entity\Pays;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setLogin('Bob');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->encoder->encodePassword(
                $admin,
                'bob4567'
            )
        );

        $manager->persist($admin);

        $user = new User();
        $user->setLogin('John');
        $user->setPassword(
            $this->encoder->encodePassword(
                $user,
                'john1234'
            )
        );

        $manager->persist($user);

        // PAYS
        if (($paysFile = fopen(__DIR__ . "/../../data/ListeDePays.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($paysFile)) !== FALSE) {
                $pays = new Pays();
                $pays->setNom($data[0]);
                $manager->persist($pays);
            }

            fclose($paysFile);
        }

        $manager->flush();
    }
}
