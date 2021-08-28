<?php


namespace App\Service;


use Throwable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormProcessingService extends AbstractController
{
    public const JSON_VALIDATION_ERROR_MESSAGE = 'JSON data was not sent correctly; check the documentation.';

    /**
     * @param Request $request
     * @param string $formType
     * @param mixed|null $dataObject
     * @param callable|null $handler
     * @param bool $clearMissing
     * @return JsonResponse
     */
    public function processJsonForm(
        Request $request,
        string $formType,
        mixed $dataObject = null,
        Callable $handler = null,
        bool $clearMissing = false
    ): JsonResponse
    {
        $content = $request->getContent();

        if ($content === '') {
            $json = [];
        } else {
            $json = json_decode($content, true);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['Error' => 'Invalid JSON syntax.'], Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm($formType, $dataObject);

        try {
            $form->submit($json, $clearMissing);
        } catch (Throwable $e) {
            return $this->json(['Error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->json(['Errors' => FormProcessingService::JSON_VALIDATION_ERROR_MESSAGE], Response::HTTP_BAD_REQUEST);
        } else {
            $dataObject = $dataObject ?: $form->getData();
            $dataObject = $handler ? $handler($dataObject) : $dataObject;
            return $this->json($dataObject, Response::HTTP_OK);
        }
    }
}