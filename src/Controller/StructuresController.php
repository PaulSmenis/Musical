<?php

namespace App\Controller;

use App\Structures\Pitch;
use App\Structures\Scale;
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
     * @Route("/scale", methods={"GET"})
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function scale(Request $request): Response
    {
        $a = new Scale(
            new Pitch($request->get('tonic'), $request->get('accidental'), $request->get('octave')),
            'zhopa',
            '1'
        );

        return $this->json($a);
    }
}