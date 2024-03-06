<?php

namespace Dontdrinkandroot\GitkiBundle\Form\Type;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TextEditType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, ['attr' => ['rows' => 15], 'label' => false])
            ->add('commitMessage', TextType::class, ['label' => 'Commit Message', 'required' => true]);
    }
}
