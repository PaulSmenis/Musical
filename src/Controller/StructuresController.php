<?php

namespace App\Controller;

use App\DTO\ScaleDTO;
use App\Form\DataTransformer\PitchToDTODataTransformer;
use App\Form\ScaleType;
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
     *     @SWG\Schema(ref=@Model(type=PitchType::class))
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
            function (PitchDTO $pitchDTO) {
                return new Pitch(
                    $pitchDTO->getName(),
                    $pitchDTO->getAccidental(),
                    $pitchDTO->getOctave()
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
     *     name="data",
     *     in="body",
     *     @SWG\Schema(ref=@Model(type=ScaleType::class))
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
        return $this->formProcessingService->processJsonForm(
            $request,
            ScaleType::class,
            new ScaleDTO,
            function (ScaleDTO $scaleDTO) {
                return new Scale(
                    $scaleDTO->getPitch(),
                    $scaleDTO->getFormula(),
                    $scaleDTO->getDegree()
                );
            }
        );
    }
}