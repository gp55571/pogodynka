<?php

namespace App\Controller;

use App\Form\ExternalAPIType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExternalAPIController extends AbstractController
{
    #[Route('/external-api', name: 'app_external_api')]
    public function index(Request $request, HttpClientInterface $httpClient): Response
    {
        $form = $this->createForm(ExternalAPIType::class, []);
        $form->handleRequest($request);

        $forecast = null;
        $error = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $latitude = $form->get('latitude')->getData();
            $longitude = $form->get('longitude')->getData();

            $apiUrl = sprintf(
                'https://api.open-meteo.com/v1/forecast?latitude=%s&longitude=%s&daily=temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=UTC',
                $latitude,
                $longitude
            );

            try {
                $response = $httpClient->request('GET', $apiUrl);
                $forecast = $response->toArray();
            } catch (ExceptionInterface $e) {
                $error = 'Nie udało się pobrać prognozy z Open-Meteo. Sprawdź podane współrzędne.';
            }
        }

        return $this->render('external_api/index.html.twig', [
            'form' => $form,
            'forecast' => $forecast,
            'error' => $error,
        ]);
    }
}
