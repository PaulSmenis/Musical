<?php


namespace App\Form;


use App\DTO\PitchDTO;
use App\Entity\Pitch;
use App\Validator\PitchConstraint;
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
            ->add('name', TextType::class, [
                'constraints' => [
                    new Choice(['choices' => Pitch::NAMES, 'message' => 'Wrong pitch passed.']),
                    new PitchConstraint(['data' => $builder->getForm()->getData()])
                ],
                'documentation' => [
                    'description' => 'Pitch name',
                    'example' => 'C'
                ]
            ])
            ->add('accidental', TextType::class, [
                'constraints' => [
                    new Choice(['choices' => Pitch::ACCIDENTALS, 'message' => 'Wrong accidental passed.'])
                ],
                'documentation' => [
                    'description' => 'Pitch accidental',
                    'example' => '##'
                ]
            ])
            ->add('octave', IntegerType::class, [
                'constraints' => [
                    new Range(['min' => 0, 'max' => 8])
                ],
                'documentation' => [
                    'description' => 'Pitch octave (SPL)',
                    'example' => '4'
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