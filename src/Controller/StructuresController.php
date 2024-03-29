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
        $pitch = $this->processPitch($request);
        if ($pitch instanceof Pitch) {
            return $this->json($pitch, Response::HTTP_OK);
        } else {
            return $pitch;
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
                $degree
            );
            $this->logger->notice($scale, [
                'pitch' => (string) $pitch,
                'formula' => $formula,
                'degree' => $degree,
                'accidental' => $accidental,
                'octave' => $octave
            ]);
        } catch (\Throwable $e) {
            $this->logger->notice($e);
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json($scale->getPitches(), Response::HTTP_OK);
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
        $pitch = $this->processPitch($request);

        if (!($pitch instanceof Pitch)) {
            return $pitch;
        }

        try {
            $quality = $request->get('quality');
            $inversion = $request->get('inversion');

            $chordDataTypesValidation = $this->validateChordDataTypes($quality, $inversion);
            if ($chordDataTypesValidation !== null) {
                return $chordDataTypesValidation;
            }

            $chord = new Chord($pitch, $quality, $inversion);
            $this->logger->notice($chord);
        } catch (\Throwable $e) {
            $this->logger->notice($e);
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json(['pitches' => $chord->getScale()->getPitches(), 'name' => (string) $chord], Response::HTTP_OK);
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
     * Validates data types of chord parameters passed in the request.
     *
     * @param $quality
     * @param $inversion
     * @return JsonResponse|null
     */
    private function validateChordDataTypes($quality, $inversion): ?JsonResponse
    {
        if (!is_null($quality) && !is_string($quality)) {
            return $this->json(['message' => 'Incorrect quality data type (available: null|string).'], Response::HTTP_BAD_REQUEST);
        } elseif (!is_null($inversion) && !is_int($inversion)) {
            return $this->json(['message' => 'Incorrect inversion data type (available: null|int).'], Response::HTTP_BAD_REQUEST);
        }
        return null;
    }

    /**
     * Extracts and validates pitch data from request and forms pitch object.
     *
     * @param $request
     * @param bool $sane_mode
     * @return JsonResponse|Pitch
     */
    private function processPitch($request, bool $sane_mode = false): JsonResponse|Pitch
    {
        $name = $request->get('name');
        $accidental = $request->get('accidental');
        $octave = $request->get('octave');

        $pitchDataTypeValidation = $this->validatePitchDataTypes($name, $accidental, $octave);

        if ($pitchDataTypeValidation !== null) {
            return $pitchDataTypeValidation;
        }

        if ($sane_mode) {
            if (in_array($accidental, ['bbb', 'bb', '##', '###']) || $accidental === null) {
                $sane_acc = ['b', 'natural', '#'];
                $accidental = $sane_acc[array_rand($sane_acc)];
            }
            if (in_array($octave, [0, 1, 2, 6, 7, 8]) || $octave === null) {
                $sane_oct = [3, 4, 5];
                $octave = $sane_oct[array_rand($sane_oct)];
            }
        }

        try {
            $pitch = new Pitch(
                $name,
                $accidental,
                $octave
            );
            $this->logger->notice($pitch);
        } catch (\Throwable $e) {
            $this->logger->notice($e);
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $pitch;
    }
}