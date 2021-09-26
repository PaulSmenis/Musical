<?php

namespace App\Controller;

use Exception;
use App\Entity\Pitch;
use App\Entity\Scale;
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
        $pitch = $this->processPitch($request);
        if ($pitch instanceof Pitch) {
            return $this->json($pitch, Response::HTTP_OK);
        } else {
            return $pitch;
        }
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
        $accidental = $request->get('accidental');
        $octave = $request->get('octave');

        $pitch = $this->processPitch($request);

        if (!($pitch instanceof Pitch)) {
            return $pitch;
        }

        try {
            $formula = $request->get('formula');
            $degree = $request->get('degree');

            $scaleDataTypesValidation = $this->validateScaleDataTypes($formula, $degree);
            if ($scaleDataTypesValidation !== null) {
                return $scaleDataTypesValidation;
            }

            $scale = new Scale(
                $pitch,
                $formula,
                $degree,
                $accidental === null,
                $octave === null
            );
        } catch (\Throwable $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($scale->getPitches(), Response::HTTP_OK);
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
            return $this->json(['message' => 'Incorrect name data type (available: null|string).'], Response::HTTP_BAD_REQUEST);
        } elseif (!is_null($accidental) && !is_string($accidental)) {
            return $this->json(['message' => 'Incorrect accidental data type (available: null|string).'], Response::HTTP_BAD_REQUEST);
        } elseif (!is_null($octave) && !is_int($octave)) {
            return $this->json(['message' => 'Incorrect octave data type (available: null|int).'], Response::HTTP_BAD_REQUEST);
        }
        return null;
    }

    /**
     * Validates data types of scale parameters passed in the request.
     *
     * @param $formula
     * @param $degree
     * @return JsonResponse|null
     */
    private function validateScaleDataTypes($formula, $degree): ?JsonResponse
    {
        if (!is_null($formula) && !is_string($formula) && !is_array($formula)) {
            return $this->json(['message' => 'Incorrect formula data type (available: null|string|array).'], Response::HTTP_BAD_REQUEST);
        } elseif (!is_null($degree) && !is_string($degree)) {
            return $this->json(['message' => 'Incorrect degree data type (available: null|string).'], Response::HTTP_BAD_REQUEST);
        }
        return null;
    }

    /**
     * Extracts and validates pitch data from request and forms pitch object.
     *
     * @param $request
     * @return JsonResponse|Pitch
     */
    private function processPitch($request): JsonResponse|Pitch
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
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $pitch;
    }
}