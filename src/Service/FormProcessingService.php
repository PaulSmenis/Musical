<?php


namespace App\Service;


use App\Form\ScaleType;
use Throwable;
use App\Form\PitchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormProcessingService extends AbstractController
{
    /**
     * Input variants:
     * Request + data object --> Will create instance based on DataObject (class only can be passed) with JSON data put into its constructor
     * Request + form type --> Will create instance and fill it up based on form type data class
     * Request + data object + form type --> Form-validating incoming data, passing it to DTO and further
     * In all cases you can pass handler to additionally process your data in the end of the procedure
     *
     * @param Request $request
     * @param string|null $formType
     * @param mixed|null $dataObject
     * @param callable|null $handler
     * @param bool $clearMissing
     * @return JsonResponse
     */
    public function processJsonForm(
        Request $request,
        ?string $formType = null,
        mixed $dataObject = null,
        ?Callable $handler = null,
        ?bool $clearMissing = false,
        ?bool $response = true
    ): JsonResponse
    {
        $content = $request->getContent();

        if ($content === '') {
            $json = [];
        } else {
            $json = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE && $json !== null) {
                return $this->json(['errors' => ['Invalid JSON syntax.']], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($formType) {
            $form = $this->createForm($formType, is_string($dataObject) ? null : $dataObject);

            try {
                $form->submit($json ?? $request, $clearMissing);
            } catch (Throwable $e) {
                return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
            }


            if ($form->isSubmitted() && !$form->isValid()) {
                $errors = [];
                foreach($form->getErrors(true) as $error) {
                    $message = $error->getMessage();
                    $message = $this->tideUpErrorMessage($message, $formType);
                    $errors[] = $message;
                };
                return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            } else {
                if (is_string($dataObject)) {
                    $dataObject = new $dataObject($form->getData());
                } else {
                    $dataObject = $dataObject ?: $form->getData();
                }

                $dataObject = $handler ? $handler($dataObject) : $dataObject;
            }
        } else {
            if ($dataObject) {
                $dataObject = new $dataObject(...$json);

                $dataObject = $handler ? $handler($dataObject) : $dataObject;
            } else {
                $dataObject = $handler ? $handler($request) : ['errors' => ['No form/data/handler has been passed to the processor.']];
                // If nothing besides request was passed, nothing would be processed
            }
        }

        if ($response) {
            return $this->json($dataObject, Response::HTTP_OK);
        } else {
            return $dataObject;
        }
    }

    /**
     * Hints affected object in "This value..." form constraint error messages depending on form
     *
     * @param string $message
     * @param string $formType
     * @return string
     */
    private function tideUpErrorMessage(string $message, string $formType): string
    {
        if (preg_match('/^This value.*/', $message)) {
            switch($formType) {
                case PitchType::class:
                case ScaleType::class:
                    return preg_replace('/This value/', 'Octave', $message);
                default:
                    return $message;
            }
        }
        return $message;
    }
}