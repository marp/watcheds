<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('email', EmailType::class, array('attr'=> array('class'=>'form-control')))
        ->add('username', TextType::class, array('attr'=> array('class'=>'form-control')))
        ->add('plainPassword', RepeatedType::class, array(
        'type' => PasswordType::class,
        'first_options'  => array('label' => 'Password', 'attr'=>array('class'=>'form-control')),
        'second_options' => array('label' => 'Repeat Password', 'attr'=>array('class'=>'form-control')),
        ))
        ->add('visibility',TextType::class, array('attr'=> array('class'=>'form-control')))
/*        ->add('termsAccepted', CheckboxType::class, array(
        'mapped' => false,
        'constraints' => new IsTrue(),
        'attr'=> array('class'=>''),
        'label_attr' => array('class'=>'form-check-label')
        ))
        ->add('recaptcha', EWZRecaptchaType::class, array(
            'attr'        => array(
                'options' => array(
                    'theme' => 'light',
                    'type'  => 'image',
                    'size'  => 'normal'
                )
            ),
            'mapped'      => false,
            'constraints' => array(
                new RecaptchaTrue()
            )
        ))*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        'data_class' => User::class,
        ));
    }
}