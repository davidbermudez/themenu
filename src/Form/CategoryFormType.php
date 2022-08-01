<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('caption')
            ->add('caption_en')
            ->add('caption_ca')
            ->add('description', TextareaType::class, [
                "attr" => array("row" => "3"),
                'required' => false,
            ])
            ->add('description_en', TextareaType::class, [
                "attr" => array("row" => "3"),
                'required' => false,
            ])
            ->add('description_ca', TextareaType::class, [
                "attr" => array("row" => "3"),
                'required' => false,
            ])
            //->add('menu')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
