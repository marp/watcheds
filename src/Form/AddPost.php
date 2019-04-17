<?php
namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AddPost extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ...
//            ->setAction($options['router']->generate('mb_post_add'))
            ->add('content', TextareaType::class, array(/*'label' => 'Content',*/
                'attr'=>['cols'=>"60", 'rows'=>"2", 'tabindex'=>"1"]))
            ->getForm()

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
//            'router' => null,
//            'data_class' => User::class,
        ));
    }
}