<?php

namespace App\Controller;

use Exception;
use App\Entity\Chord;
use App\Entity\Pitch;
use App\Entity\Scale;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api/structure")
 * @SWG\Tag(name="Creation of pitches and pitch structures")
 *
 * @SWG\Response(
 *     response=400,
 *     description="Bad request.",
 *     @SWG\Items(
 *         @SWG\Property(
 *             property="message",
 *             type="string",
 *             example="Foo."
 *         )
 *     )
 * )
 */
class StructuresController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $structuresLogger)
    {
        $this->logger = $structuresLogger;
    }

    /**
     * Generates pitch. Non-passed value counts as "random".
     *
     * Allowed octaves are 0-8 SPL.
     * Allowed accidentals are triple at max.
     * Please note that 'natural' accidental is also a valid parameter / returning value.
     * Default value is random.
     *
     * @Route("/pitch", methods={"GET"})
     *
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     @SWG\Schema(
     *         @SWG\Property(property="name", type="string", example="G"),
     *         @SWG\Property(property="accidental", type="string", example="#"),
     *         @SWG\Property(property="octave", type="integer", example=5)
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Some pitch has been generated and returned successfully.",
     *     @SWG\Items(
     *         @SWG\Property(property="name", type="string", example="G"),
     *         @SWG\Property(property="accidental", type="string", example="#"),
     *         @SWG\Property(property="octave", type="integer", example=5)
     *     )
     * )
     *
     * @return Response
     * @throws Exception
     */
    public function pitch(Request $request): Response
    {
        $name = $request->get('name');
        $accidental = $request->get('accidental');
        $octave = $request->get('octave');

        try {
            Pitch::validatePitchDataTypes($name, $accidental, $octave);
            $pitch = new Pitch(
                $name,
                $accidental,
                $octave
            );
            $this->logger->notice($pitch);
            return $this->json($pitch, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->notice($e);
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Generates scale. Non-passed value counts as "random".
     *
     * Basically an array of pitches.
     *
     * @Route("/scale", methods={"GET"})
     *
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     @SWG\Schema(
     *         @SWG\Property(property="name", type="string", example="G"),
     *         @SWG\Property(property="accidental", type="string", example="#"),
     *         @SWG\Property(property="octave", type="integer", example=5),
     *         @SWG\Property(property="formula", type="string", example="minor"),
     *         @SWG\Property(property="degree", type="string", example="b3")
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Some structure (i.e. pitches array) has been created and returned successfully.",
     *     @SWG\Property(property="answer", type="array",
     *             @SWG\Items(
     *                 @SWG\Property(property="name", type="string", example="G"),
     *                 @SWG\Property(property="accidental", type="string", example="#"),
     *                 @SWG\Property(property="octave", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function scale(Request $request): Response
    {
        $accidental = $request->get('accidental');
        $octave = $request->get('octave');

        try {
            $formula = $request->get('formula');
            $degree = $request->get('degree');
            Scale::validateScaleDataTypes($formula, $degree);

            $scale = new Scale(
                [
                    'name' => $request->get('name'),
                    'accidental' => $request->get('accidental'),
                    'octave' => $request->get('octave')
                ],
                $formula,
                $degree
            );
            $this->logger->notice($scale, [
                'pitch' => (string) $scale,
                'formula' => $formula,
                'degree' => $degree,
                'accidental' => $accidental,
                'octave' => $octave
            ]);
        } catch (\Throwable $e) {
            $this->logger->notice($e);
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json((string) $scale, Response::HTTP_OK);
    }

    /**
     * Generates chord. Non-passed value counts as "random".
     *
     * @Route("/chord", methods={"GET"})
     *
     * @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     @SWG\Schema(
     *         @SWG\Property(property="name", type="string", example="G"),
     *         @SWG\Property(property="accidental", type="string", example="#"),
     *         @SWG\Property(property="octave", type="integer", example=5),
     *         @SWG\Property(property="quality", type="string", example="m7b5"),
     *         @SWG\Property(property="inversion", type="integer", example=3),
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Some structure (i.e. pitches array) has been created and returned successfully.",
     *     @SWG\Property(property="answer", type="array",
     *             @SWG\Items(
     *                 @SWG\Property(property="name", type="string", example="G"),
     *                 @SWG\Property(property="accidental", type="string", example="#"),
     *                 @SWG\Property(property="octave", type="integer", example=5)
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function chord(Request $request): Response
    {
        $name = $request->get('name');
        $accidental = $request->get('accidental');
        $octave = $request->get('octave');
        $quality = $request->get('quality');
        $inversion = $request->get('inversion');

        try {

            Pitch::validatePitchDataTypes($name, $accidental, $octave);
            $pitch = new Pitch(
                $name,
                $accidental,
                $octave
            );

            Chord::validateChordDataTypes($quality, $inversion);
            $chord = new Chord($pitch, $quality, $inversion);
            $this->logger->notice($chord);
        } catch (\Throwable $e) {
            $this->logger->notice($e);
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json(['pitches' => $chord->getScale()->getPitches(), 'name' => (string) $chord], Response::HTTP_OK);
    }
}