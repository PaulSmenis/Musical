<?php

namespace App\Controller;

use App\Entities\Pitch;
use App\Entities\Scale;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

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
     * Generates and returns a certain pitch structure (interval, chord, scale) built on tonic.
     *
     * Basically an array of pitches.
     *
     * @Route("/scale", methods={"GET"})
     * @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     type="string",
     *     description="Scale or chord tonic name. Set to random by default.",
     *     required=false
     * ),
     * @SWG\Parameter(
     *     name="accidental",
     *     in="query",
     *     type="string",
     *     description="Tonic accidental. Set to random by default.",
     *     required=false
     * ),
     * @SWG\Parameter(
     *     name="octave",
     *     in="query",
     *     type="integer",
     *     description="Tonic octave. Set to random by default.",
     *     required=false
     * ),
     * @SWG\Parameter(
     *     name="degree",
     *     in="query",
     *     type="string",
     *     description="Which degree of a given structure tonic is (e.g. b2). Set to 1 by default (i.e. the tonic).",
     *     required=false
     * ),
     * @SWG\Parameter(
     *     name="formula",
     *     in="query",
     *     type="string",
     *     description="Can be set via common name (i.e. 'harmonic minor') but you can pass string of the form '3,1,b5' as well. Set to major by default.",
     *     required=false
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Some structure (i.e. pitches array) has been created and returned successfully",
     *     @SWG\Property(property="answer", type="array",
     *         @SWG\Items(
     *             @SWG\Property(property="name", type="string", example="G"),
     *             @SWG\Property(property="accidental", type="string", example="#"),
     *             @SWG\Property(property="octave", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     * @SWG\Tag(name="Creation of pitches and pitch structures")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function scale(Request $request): Response
    {
        $scale = new Scale(
            new Pitch(
                $request->get('name') ?? null,
                $request->get('accidental') ?? null,
                $request->get('octave') ?? null
            ),
            $request->get('formula') ?? 'major',
            $request->get('degree') ?? 1
        );

        return $this->json($scale);
    }
}