<?php


namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Paste;
use DateTime;

class PasteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'pastebin.paste.title',
            ])
            ->add('text', TextareaType::class, [
                'label' => 'pastebin.paste.text',
            ])
            ->add('expireDate', TextType::class, [
                'label' => 'pastebin.paste.expireDate',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#paste_expireDate'
                ],
                'required' => false
            ])
            ->add('save', SubmitType::class, [
            'label' => 'save'
        ]);

        $builder->get('expireDate')->addModelTransformer(new CallbackTransformer(
            function (?DateTime $dateTime) {
                if (!$dateTime) {
                    return;
                }

                return $dateTime->format('d.m.Y H:i');
            },
            function (?string  $dateString) {
                if (!$dateString) {
                    return;
                }

                return new DateTime($dateString);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['id' => 'PasteForm'],
            'data_class' => Paste::class
        ]);
    }
}