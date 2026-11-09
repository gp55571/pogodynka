<?php

namespace App\Command;

use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:city',
    description: 'Display weather forecast for a city',
)]
class WeatherCityCommand
{
    public function __construct(
        private WeatherUtil $weatherUtil,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Country code (e.g. PL)')] string $countryCode,
        #[Argument('City name')] string $city,
    ): int {
        $countryCode = strtoupper($countryCode);

        $measurements = $this->weatherUtil->getWeatherForCountryAndCity($countryCode, $city);

        if (count($measurements) === 0) {
            $io->warning(sprintf('No weather data found for %s, %s.', $city, $countryCode));

            return Command::FAILURE;
        }

        $io->title(sprintf('Location: %s, %s', $city, $countryCode));

        $io->table(
            ['Date', 'Temperature', 'Pressure', 'Humidity', 'Weather condition'],
            array_map(fn($m) => [
                $m->getDate()->format('Y-m-d'),
                $m->getTemperature() . ' °C',
                $m->getPressure() . ' hPa',
                $m->getHumidity() . '%',
                $m->getWeatherCondition(),
            ], $measurements)
        );

        return Command::SUCCESS;
    }
}
