<?php

namespace App\Controller;

use App\Repository\DestinationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(DestinationRepository $destinationRepository)
    {
        $destinations = $destinationRepository->getDestinationsWithLatLng();
        $destinationsView = [];

        foreach($destinations as $destination) {
            $destinationsView[] = [
                'lat' => $destination->getLat(),
                'lng' => $destination->getLng(),
                'ville' => $destination->getVille()
            ];
        }

        return $this->render('index/index.html.twig', [
            'destinationsJs' => $destinationsView
        ]);
    }
}
