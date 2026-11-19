<?php

namespace App\Controller;

use App\Entity\Measurement;
use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherApiController extends AbstractController
{
    #[Route('/api/v1/weather', name: 'app_weather_api', methods: ['GET'])]
    public function index(
        #[MapQueryParameter] string $city,
        #[MapQueryParameter] string $country,
        WeatherUtil $weatherUtil,
        #[MapQueryParameter] string $format = 'json',
        #[MapQueryParameter('twig')] bool $twig = false,
    ): Response {

        if (!in_array($format, ['json', 'csv'], true)) {
            return $this->json(
                ['error' => 'Parametr format musi mieć wartość json albo csv.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $measurements = $weatherUtil->getWeatherForCountryAndCity(
            $country,
            $city
        );

        if ($format === 'csv') {
            if ($twig) {
                $response = $this->render('weather_api/index.csv.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $measurements,
                ]);
                $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');

                return $response;
            }

            $lines = ['city,country,date,celsius,fahrenheit'];

            foreach ($measurements as $m) {
                $lines[] = sprintf(
                    '%s,%s,%s,%s,%s',
                    $city,
                    $country,
                    $m->getDate()->format('Y-m-d'),
                    (float)$m->getTemperature(),
                    $m->getFahrenheit()
                );
            }

            return new Response(
                implode("\n", $lines) . "\n",
                Response::HTTP_OK,
                ['Content-Type' => 'text/csv; charset=UTF-8']
            );
        }

        if ($twig) {
            $response = $this->render('weather_api/index.json.twig', [
                'city' => $city,
                'country' => $country,
                'measurements' => $measurements,
            ]);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->json([
            'city' => $city,
            'country' => $country,
            'measurements' => array_map(
                fn(Measurement $m) => [
                    'date' => $m->getDate()->format('Y-m-d'),
                    'celsius' => (float)$m->getTemperature(),
                    'fahrenheit' => $m->getFahrenheit(),
                    'humidity' => $m->getHumidity(),
                    'pressure' => $m->getPressure(),
                    'weatherCondition' => $m->getWeatherCondition(),
                ],
                $measurements
            ),
        ]);
    }
}
