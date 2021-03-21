<?php

namespace App\Controller;

use App\Serializer\Normalizer\PitchNormalizer;
use App\Structures\Pitch;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api/structures")
 */
class StructuresController extends AbstractController
{
    /**
     * @Route("/pitch", name="pitch", methods={"GET"})
     * @return Response
     */
    public function pitch(): Response
    {
        return $this->json(new Pitch);
    }
}