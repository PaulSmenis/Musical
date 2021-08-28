<?php


namespace App\Form;


use App\Entity\Pitch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PitchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'documentation' => [
                    'description' => 'Pitch name',
                    'example' => 'C'
                ]
            ])
            ->add('accidental', TextType::class, [
                'documentation' => [
                    'description' => 'Pitch accidental',
                    'example' => '##'
                ]
            ])
            ->add('octave', IntegerType::class, [
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
            'data_class' => Pitch::class,
        ]);
    }
}