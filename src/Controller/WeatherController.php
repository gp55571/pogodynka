<?php

namespace App\Controller;

use App\Repository\LocationRepository;
use App\Repository\MeasurementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherController extends AbstractController
{
    #[Route('/weather/{city}/{country}', name: 'app_weather_with_country')]
    #[Route('/weather/{city}', name: 'app_weather')]
    public function city(
        string $city,
        LocationRepository $locationRepository,
        MeasurementRepository $repository,
        ?string $country = null
    ): Response {
        $city_code = ['city' => $city];

        if ($country !== null) {
            $city_code['country'] = $country;
        }

        $location = $locationRepository->findOneBy(['city' => $city_code]);
        $measurements = $repository->findByLocation($location);

        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }
}
