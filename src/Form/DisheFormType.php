<?php

namespace App\Form;

use App\Entity\Dishes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DisheFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('caption', TextType::class,[
                'required' => true,
            ])
            ->add('caption_en')
            ->add('caption_ca')
            ->add('description')
            ->add('description_en')
            ->add('description_ca')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'caption',
                'disabled' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dishes::class,
        ]);
    }
}
