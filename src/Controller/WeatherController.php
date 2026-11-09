<?php

namespace App\Controller;

use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
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
        WeatherUtil $util,
        ?string $country = null
    ): Response {
        $criteria = ['city' => $city];

        if ($country !== null) {
            $criteria['country'] = $country;
        }

        $location = $locationRepository->findOneBy($criteria);

        if ($location === null) {
            throw $this->createNotFoundException(
                'Nie znaleziono podanej lokalizacji.'
            );
        }

        $measurements = $util->getWeatherForLocation($location);

        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }
}
