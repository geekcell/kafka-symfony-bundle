<?php

declare(strict_types=1);

namespace GeekCell\KafkaBundle\DependencyInjection;

use GeekCell\KafkaBundle\Contracts\Event;
use GeekCell\KafkaBundle\EventSubscriber\KafkaSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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

        $this->registerAvroConfiguration(
            $processed['avro'],
            $container
        );
        $this->registerKafkaEventsForSubscriber(
            $processed['events'],
            $container
        );
        $this->registerKafkaConfiguration(
            $processed['kafka'],
            $container
        );
    }

    private function registerAvroConfiguration(
        array $avroConfig,
        ContainerBuilder $container,
    ): void
    {
        $container->setParameter(
            'geek_cell_kafka.avro.schema_registry_url',
            $avroConfig['schema_registry_url'],
        );

        $container->setParameter(
            'geek_cell_kafka.avro.schemas.defaults',
            $avroConfig['schemas']['defaults'],
        );
    }

    private function registerKafkaEventsForSubscriber(
        array $eventConfig,
        ContainerBuilder $container,
    ): void
    {
        $subscriberClass = KafkaSubscriber::class;

        $globs = array_map(function ($resource) use ($container) {
            return sprintf(
                '%s/%s',
                $container->getParameter('kernel.project_dir'),
                $resource,
            );
        }, $eventConfig['lookup']);

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

    private function registerKafkaConfiguration(
        array $kafkaConfig,
        ContainerBuilder $container,
    ): void
    {
        // RdKafka configuration
        $rdKafkaConfDefinition = new Definition(\RdKafka\Conf::class);
        $container->setDefinition(\RdKafka\Conf::class, $rdKafkaConfDefinition);

        // ... default configuration
        $rdKafkaConfDefinition->addMethodCall(
            'set',
            ['bootstrap.servers', $kafkaConfig['brokers']]
        );

        // ... global configuration
        foreach ($kafkaConfig['global'] as $key => $value) {
            $rdKafkaConfDefinition->addMethodCall(
                'set',
                [strval($key), strval($value)],
            );
        }

        // ... topic configuration
        foreach ($kafkaConfig['topic'] as $key => $value) {
            $rdKafkaConfDefinition->addMethodCall(
                'set',
                ['topic.'.strval($key), strval($value)],
            );
        }

        // See: https://github.com/arnaud-lb/php-rdkafka#performance--low-latency-settings
        if (function_exists('pcntl_sigprocmask')) {
            pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
            $rdKafkaConfDefinition->addMethodCall(
                'set',
                ['internal.termination.signal', SIGIO],
            );
        } else {
            $rdKafkaConfDefinition->addMethodCall(
                'set',
                ['queue.buffering.max.ms', 1],
            );
        }

        // RdKafka producer
        $rdKafkaProducerDefinition = new Definition(
            \RdKafka\Producer::class,
            [$rdKafkaConfDefinition],
        );
        $rdKafkaProducerDefinition->addMethodCall(
            'addBrokers',
            [$kafkaConfig['brokers']],
        );
        $container->setDefinition(
            \RdKafka\Producer::class,
            $rdKafkaProducerDefinition,
        );

        // RdKafka consumer
        $rdKafkaConsumerDefinition = new Definition(
            \RdKafka\KafkaConsumer::class,
            [$rdKafkaConfDefinition],
        );
        $rdKafkaConsumerDefinition->addMethodCall(
            'addBrokers',
            [$kafkaConfig['brokers']],
        );
        $container->setDefinition(
            \RdKafka\KafkaConsumer::class,
            $rdKafkaConsumerDefinition,
        );
    }
}
