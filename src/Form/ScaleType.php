<?php


namespace App\Form;


use App\DTO\PitchDTO;
use App\DTO\ScaleDTO;
use App\Entity\Pitch;
use App\Entity\Scale;
use App\Form\DataTransformer\PitchToDTODataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Validator\ResultingScaleDegreesAreNotOutOfRangeConstraint;

class ScaleType extends AbstractType
{
    private $transformer;

    public function __construct(PitchToDTODataTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('pitch', PitchType::class, [
                'documentation' => [
                    'description' => 'Reference pitch used for building the scale',
                ],
                'constraints' => [
                    new ResultingScaleDegreesAreNotOutOfRangeConstraint(['data' => $builder->getForm()->getData()])
                ],
            ])
            ->add('formula', TextType::class, [
                'documentation' => [
                    'description' =>
                        <<<INFO
                        Can be set via common name (i.e. "harmonic minor") but you can pass string of the form "3,1,b5" as well.
                        Octaves are set according to the formula and common sense.
                        Hence, pitch octaves are inverted based on pitch order and with regard to passed reference pitch octave value.
                        Set to major by default. 
                        INFO,
                    'example' => '1,b3,#4,5,b7'
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => $this->addScalesToRegex(
                            "^([b]{0,3}[1-7]|[#]{0,3}[1-7])(?:\,[b]{0,3}[1-7]|\,[#]{0,3}[1-7])*$",
                            array_keys(Scale::COMMON_SCALES)
                        ),
                        'message' => 'Passed formula is not valid.'
                    ])
                ]
            ])
            ->add('degree', TextType::class, [
                'documentation' => [
                    'description' => 'Which degree of a given structure reference pitch is. Set to 1 by default (i.e. the tonic).',
                    'example' => 'b3'
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => "/^[b]{0,3}[1-7]$|^[#]{0,3}[1-7]$/",
                        'message' => "Passed degree is not valid."
                    ])
                ]
            ])
        ;

        $builder->get('pitch')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScaleDTO::class,
            'empty_data' => null,
        ]);
    }

    /**
     * @param string $regex
     * @param array $scales
     * @return string
     */
    private function addScalesToRegex(string $regex, array $scales): string
    {
        return '/' . $regex . implode(
                array_map(
                    function ($scale) {
                        return '|^' . $scale . '$';
                    },
                    $scales
                )
            ) . '/';
    }
}