<?php

namespace App\Controller;

use App\Serializer\Normalizer\PitchNormalizer;
use App\Structures\Pitch;
use Swagger\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use App\Serializer\Normalizer;

/**
 * @Route("/api/structures")
 */
class StructuresController extends AbstractController
{
    /**
     * Returns randomly generated pitch
     *
     * Allowed octaves are 0-8 SPL.
     * Allowed accidentals are triple at max.
     * Please note that 'natural' accidental is also a valid returning value.
     *
     * @Route("/pitch", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Some pitch has been generated and returned successfully.",
     *    @SWG\Items(
     *        @SWG\Property(property="name", type="string", example="G"),
     *        @SWG\Property(property="accidental", type="string", example="#"),
     *        @SWG\Property(property="octave", type="integer", example=5)
     *    )
     * )
     * @SWG\Tag(name="Creation of pitches and pitch structures")
     */
    public function pitch(): Response
    {
        return $this->json(new Pitch);
    }

    /**
     * @Route("/scale", methods={"GET"})
     */
    public function scale(): Response
    {
        return $this->json(new Pitch);
    }
}