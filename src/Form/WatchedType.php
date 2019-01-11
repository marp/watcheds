<?php
namespace App\Form;

use App\Entity\Watched;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WatchedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder->add('', ChoiceType::class, array(
//            'choices'  => array(
//                'Verified' => 1,
//                'N/A' => 0  ),
//            'required'=>true,
//            'label'=>false,
//            'attr'=>array('class'=>'form-control')
//        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            // 'data_class' => User::class
        ));
    }
}