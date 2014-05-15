<?php

namespace Ekyna\Bundle\OrderBundle\Settings;

use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * OrderSettingsSchema.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class OrderSettingsSchema extends AbstractSchema
{
    /**
     * @var array
     */
    protected $defaults;

    /**
     * @param array $defaults
     */
    public function __construct(array $defaults = array())
    {
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function buildSettings(SettingsBuilderInterface $builder)
    {
        $builder
            ->setDefaults(array_merge(array(
                'document_footer' => 'Pied de page des documents',
            ), $this->defaults))
            ->setAllowedTypes(array(
                'document_footer' => 'string',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('document_footer', 'textarea', array(
                'label'       => 'ekyna_order.field.document_footer',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'advanced',
                ),
                'constraints' => array(
                    new NotBlank()
                )
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'ekyna_order.order.label.plural';
    }

    /**
     * {@inheritDoc}
     */
    public function getShowTemplate()
    {
        return 'EkynaOrderBundle:Settings:show.html.twig';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormTemplate()
    {
        return 'EkynaOrderBundle:Settings:form.html.twig';
    }

    public function getName()
    {
        return 'ekyna_order_settings';
    }
}