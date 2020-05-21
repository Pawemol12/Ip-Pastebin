<?php


namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MyPastesSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'pastebin.paste.title',
                'required' => false
            ])
            ->add('code', TextType::class, [
                'label' => 'pastebin.paste.code',
                'required' => false
            ])
            ->add('createDateFrom', TextType::class, [
                'label' => 'dateFrom',
                'required' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#my_pastes_search_form_createDateFrom'
                ]
            ])
            ->add('createDateTo', TextType::class, [
                'label' => 'dateTo',
                'required' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#my_pastes_search_form_createDateTo'
                ]
            ])
            ->add('expireDateFrom', TextType::class, [
                'label' => 'dateFrom',
                'required' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#my_pastes_search_form_expireDateFrom'
                ]
            ])
            ->add('expireDateTo', TextType::class, [
                'label' => 'dateTo',
                'required' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#my_pastes_search_form_expireDateTo'
                ]
            ])
            ->add('search', SubmitType::class, [
                'label' => 'search'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['id' => 'MyPastesSearchForm'],
        ]);
    }
}