<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\DependencyInjection;

use GeekCell\KafkaBundle\Contracts\Event;
use GeekCell\KafkaBundle\EventSubscriber\KafkaSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GeekCellKafkaExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $locator = new FileLocator(__DIR__ . '/../../config');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $processed = $this->processConfiguration($configuration, $configs);

        $this->registerKafkaEventsForSubscriber($processed, $container);
    }

    private function registerKafkaEventsForSubscriber(
        array $configs,
        ContainerBuilder $container
    ): void
    {
        $subscriberClass = KafkaSubscriber::class;

        $globs = array_map(function ($resource) use ($container) {
            return sprintf(
                '%s/%s',
                $container->getParameter('kernel.project_dir'),
                $resource,
            );
        }, $configs['events']['resources']);

        $finder = new Finder();
        $finder->in($globs)->name('*.php');

        foreach ($finder as $file) {
            $matches = [];
            preg_match('/namespace (.*);/', $file->getContents(), $matches);
            if (count($matches) < 2) {
                continue;
            }

            $eventClass = sprintf(
                "%s\\%s",
                $matches[1],
                $file->getBasename('.php')
            );

            if (!class_exists($eventClass) ||
                !is_subclass_of($eventClass, Event::class)) {
                continue;
            }

            call_user_func(
                [$subscriberClass, 'addSubscribedEvent'],
                $eventClass,
            );
        }
    }
}
