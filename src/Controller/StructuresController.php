<?php

namespace App\Controller;

use Exception;
use App\Entity\Pitch;
use App\Entity\Scale;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    /**
     * Returns randomly generated pitch.
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

        $pitchDataTypeValidation = $this->validatePitchDataTypes($name, $accidental, $octave);

        if ($pitchDataTypeValidation !== null) {
            return $pitchDataTypeValidation;
        }

        try {
            $pitch = new Pitch(
                $name,
                $accidental,
                $octave
            );
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($pitch, Response::HTTP_OK);
    }

    /**
     * Generates and returns a certain pitch structure (interval, chord, scale) built on tonic.
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
     *         @SWG\Property(property="degree", type="integer", example=5)
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
        try {
            $name = $request->get('name');
            $accidental = $request->get('accidental');
            $octave = $request->get('octave');

            $pitchDataTypeValidation = $this->validatePitchDataTypes($name, $accidental, $octave);
            if ($pitchDataTypeValidation) {
                return $pitchDataTypeValidation;
            }

            $pitch = new Pitch(
                $name,
                $accidental,
                $octave
            );
        } catch (\Throwable $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $scale = new Scale(
                $pitch,
                $request->get('formula'),
                $request->get('degree'),
            );
        } catch (\Throwable $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($scale, Response::HTTP_OK);
    }

    /**
     * Validates data types of pitch parameters passed in the request.
     *
     * @param $name
     * @param $accidental
     * @param $octave
     * @return JsonResponse|null
     */
    private function validatePitchDataTypes($name, $accidental, $octave): ?JsonResponse
    {
        if (!is_null($name) && !is_string($name)) {
            return $this->json(['error' => 'Incorrect name data type (available: null|string).'], Response::HTTP_BAD_REQUEST);
        } elseif (!is_null($accidental) && !is_string($accidental)) {
            return $this->json(['error' => 'Incorrect accidental data type (available: null|string).'], Response::HTTP_BAD_REQUEST);
        } elseif (!is_null($octave) && !is_int($octave)) {
            return $this->json(['error' => 'Incorrect octave data type (available: null|int).'], Response::HTTP_BAD_REQUEST);
        }
        return null;
    }
}