<?php

namespace App\Controller;

use App\Service\FormProcessingService;
use Symfony\Component\Form\Form;
use Throwable;
use Exception;
use App\Entity\Pitch;
use App\Entity\Scale;
use App\Form\PitchType;
use App\DTO\PitchDTO;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\AbstractType;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * @Route("/api/structures")
 * @SWG\Tag(name="Creation of pitches and pitch structures")
 *
 * @SWG\Response(
 *     response=500,
 *     description="Internal server error.",
 *    @SWG\Items(
 *        @SWG\Property(property="message", type="string", example="Pitch is out of range from below. Cannot be lower than C0."),
 *        @SWG\Property(property="file", type="string", example="/var/www/symfony/src/Entity/Pitch.php"),
 *        @SWG\Property(property="line", type="integer", example="278")
 *    )
 * )
 */
class StructuresController extends AbstractController
{
    /**
     * @var FormProcessingService
     */
    private $formProcessingService;

    public function __construct(FormProcessingService $formProcessingService) {
        $this->formProcessingService = $formProcessingService;
    }

    /**
     * Returns randomly generated pitch
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
     *     description="Request data. Preferably JSON.",
     *     required=false,
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(
     *             @SWG\Property(property="name", type="string", example="G"),
     *             @SWG\Property(property="accidental", type="string", example="#"),
     *             @SWG\Property(property="octave", type="integer", example=5)
     *         )
     *     )
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Some pitch has been generated and returned successfully.",
     *    @SWG\Items(
     *        @SWG\Property(property="name", type="string", example="G"),
     *        @SWG\Property(property="accidental", type="string", example="#"),
     *        @SWG\Property(property="octave", type="integer", example=5)
     *    )
     * )
     *
     * @return Response
     * @throws Exception
     */
    public function pitch(Request $request): Response
    {
        return $this->formProcessingService->processJsonForm(
            $request,
            PitchType::class,
            new PitchDTO,
            function (PitchDTO $pitchTypeDTO) {
                return new Pitch(
                    $pitchTypeDTO->getName(),
                    $pitchTypeDTO->getAccidental(),
                    $pitchTypeDTO->getOctave()
                );
            }
        );
    }

    /**
     * Generates and returns a certain pitch structure (interval, chord, scale) built on tonic
     *
     * Basically an array of pitches.
     *
     * @Route("/scale", methods={"GET"})
     *
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
     *     description="
     * Can be set via common name (i.e. 'harmonic minor') but you can pass string of the form '3,1,b5' as well.
     * Octaves are set according to the formula and common sense.
     * Hence, pitch octaves are inverted based on pitch order and with regard to passed reference pitch octave value.
     * Set to major by default.",
     *     required=false
     * )
     *
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
     *
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function scale(Request $request): Response
    {
        try {
            $pitch = new Pitch(
                $request->get('name'),
                $request->get('accidental'),
                $request->get('octave')
            );
        } catch (Throwable $e) {
            return $this->json($e, Response::HTTP_BAD_REQUEST);
        }

        try {
            $scale = new Scale(
                $pitch,
                $request->get('formula') ?? 'major',
                $request->get('degree') ?? '1'
            );
        } catch (Throwable $e) {
            return $this->json($e, Response::HTTP_BAD_REQUEST);
        }

        return $this->json($scale);
    }
}