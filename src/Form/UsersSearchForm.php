<?php


namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UsersSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'user.username',
                'required' => false
            ])

            ->add('createDateFrom', TextType::class, [
                'label' => 'dateFrom',
                'required' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#users_search_form_createDateFrom'
                ]
            ])
            ->add('createDateTo', TextType::class, [
                'label' => 'dateTo',
                'required' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#users_search_form_createDateTo'
                ]
            ])

            ->add('lastLoginDateFrom', TextType::class, [
                'label' => 'dateFrom',
                'required' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#users_search_form_lastLoginDateFrom'
                ]
            ])
            ->add('lastLoginDateTo', TextType::class, [
                'label' => 'dateTo',
                'required' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#users_search_form_lastLoginDateTo'
                ]
            ])

            ->add('search', SubmitType::class, [
                'label' => 'search'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['id' => 'UsersSearchForm'],
        ]);
    }
}