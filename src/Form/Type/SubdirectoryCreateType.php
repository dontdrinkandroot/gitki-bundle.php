<?php

namespace Dontdrinkandroot\GitkiBundle\Form\Type;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SubdirectoryCreateType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'dirname',
                TextType::class,
                [
                    'label' => 'Foldername',
                    'required' => true,
                ]
            )
            ->add('submit', SubmitType::class);
    }
}
