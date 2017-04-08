<?php

namespace Dontdrinkandroot\GitkiBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class TextEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, ['attr' => ['rows' => 15], 'label' => false])
            ->add('commitMessage', TextType::class, ['label' => 'Commit Message', 'required' => true]);
    }
}
