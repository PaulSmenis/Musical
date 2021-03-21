<?php

namespace App\Controller;

use App\Structures\Pitch;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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