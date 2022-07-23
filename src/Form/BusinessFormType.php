<?php

namespace App\Form;

use App\Entity\Business;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Contracts\Translation\TranslatorInterface;

use App\Entity\Country;

class BusinessFormType extends AbstractType
{        
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {   
        //$locale = $this->getLocale();
        $builder
            ->add('caption', TextType::class, ['label' => 'form.business.caption'])
            ->add('address', TextType::class)
            ->add('city', TextType::class)
            ->add('postcode', TextType::class, ['attr' => ['maxlength' => 5]])
            ->add('phone', TextType::class, ['attr' => ['maxlength' => 12]])            
            ->add('state', TextType::class)
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'choice_label' => 'name_en'
            ])
            //->add('date_created')
            //->add('date_modify')
            //->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Business::class,
        ]);
    }
}
