<?php


namespace App\Form;


use App\DTO\PitchDTO;
use App\Entity\Pitch;
use App\Form\DataTransformer\PitchToDTODataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PitchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('name', TextType::class, [
                'documentation' => [
                    'description' => 'Pitch name',
                    'example' => 'C'
                ],
                'constraints' => [
                    new Choice([
                        'choices' => Pitch::NAMES,
                        'message' => 'Wrong pitch passed. Should be one of: ' . implode(', ', Pitch::NAMES)
                    ])
                ]
            ])
            ->add('accidental', TextType::class, [
                'documentation' => [
                    'description' => 'Pitch accidental',
                    'example' => '##'
                ],
                'constraints' => [
                    new Choice([
                        'choices' => Pitch::ACCIDENTALS,
                        'message' => 'Wrong accidental passed. Should be one of: ' . implode(', ', Pitch::ACCIDENTALS)
                    ])
                ]
            ])
            ->add('octave', IntegerType::class, [
                'documentation' => [
                    'description' => 'Pitch octave (SPL)',
                    'example' => '4'
                ],
                'constraints' => [
                    new Range(['min' => 0, 'max' => 8])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PitchDTO::class,
            'empty_data' => null
        ]);
    }
}