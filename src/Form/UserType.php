<?php


namespace App\Form;

use App\Enum\UserRolesEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use App\Enum\UserTypeEnum;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'user.username',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => $options['mode'] == 'add',
                'invalid_message' => 'passwordsMustMatch',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => $options['type'] == UserTypeEnum::REGISTER,
                'first_options'  => ['label' => 'registration.password'],
                'second_options' => ['label' => 'registration.repeatPassword'],
            ])
            ->add('save', SubmitType::class, [
            'label' => $options['type'] == UserTypeEnum::REGISTER ? 'registration.createAccount' : 'save'
        ]);

        if ($options['type'] == UserTypeEnum::ADMIN)
        {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'user.roles',
                'choices' => UserRolesEnum::CHOICES,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'class' => 'selectpicker',
                ],
            ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => ['id' => 'UserForm'],
            'data_class' => User::class,
            'type' => UserTypeEnum::ADMIN,
            'mode' => 'add'
        ]);
    }
}