<?php

namespace App\Command;

use App\Geocoding\IGeocoding;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestGeocodingCommand extends Command
{
    protected static $defaultName = 'app:test-geocoding';
    private $geocodingService;

    public function __construct(IGeocoding $geocodingService)
    {
      $this->geocodingService = $geocodingService;
      parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Tests geocoding configured service')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $geocodeClass = get_class($this->geocodingService);
        $coordinates = $this->geocodingService->getLatLon('Lyon, France');

        $io->success('Geocode is : ' . $geocodeClass);
        $io->success('From this service, Lyon is : ' . $coordinates->getLat() . ', ' . $coordinates->getLng());

        return 0;
    }
}
