<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle;

use GeekCell\KafkaBundle\DependencyInjection\CompilerPass\AddFactoriesCompilerPass;
use GeekCell\KafkaBundle\DependencyInjection\GeekCellKafkaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class GeekCellKafkaBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new GeekCellKafkaExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Add compiler passes
        $container->addCompilerPass(new AddFactoriesCompilerPass());
    }
}
