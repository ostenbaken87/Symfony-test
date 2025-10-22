<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название книги',
                'constraints' => [
                    new Assert\NotBlank(message: 'Название обязательно'),
                    new Assert\Length(min: 1, max: 255),
                ],
            ])
            ->add('author', EntityType::class, [
                'class' => Author::class,
                'choice_label' => 'name',
                'label' => 'Автор',
                'placeholder' => 'Выберите автора',
                'constraints' => [
                    new Assert\NotNull(message: 'Автор обязателен'),
                ],
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN',
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 50),
                ],
            ])
            ->add('publishedYear', IntegerType::class, [
                'label' => 'Год издания',
                'required' => false,
                'constraints' => [
                    new Assert\Range(min: 1000, max: 2100),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}

