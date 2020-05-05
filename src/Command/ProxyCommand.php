<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Command;

use Paxal\Phproxy\PAC\ServerFactory;
use Paxal\Phproxy\Proxy\Authenticator\AuthenticatorFactory;
use Paxal\Phproxy\Proxy\ConnectionHandler;
use Paxal\Phproxy\Proxy\DataHandlerFactory;
use Paxal\Phproxy\Translator\TranslatorBuilder;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\SecureServer;
use React\Socket\TcpServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProxyCommand extends Command
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoopInterface $loop, LoggerInterface $logger)
    {
        parent::__construct();
        $this->loop = $loop;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setName('run')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Configuration file.')
            ->addOption('save', null, InputOption::VALUE_NONE, 'Save configuration file.')
            ->addOption('auth', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Authorization, eg --auth user:password')
            ->addOption('translate', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'DNS Translations, eg --translate google.com=127.0.0.1')
            ->addOption('ssl', null, InputOption::VALUE_NONE, 'Activate SSL (needs --cert and --key)')
            ->addOption('ssl-cert', null, InputOption::VALUE_REQUIRED, 'SSL certificate PEM file')
            ->addOption('ssl-key', null, InputOption::VALUE_REQUIRED, 'SSL private key PEM file')
            ->addOption('ssl-passphrase', null, InputOption::VALUE_REQUIRED, 'SSL pass phrase for private key')
            ->addOption('pac', null, InputOption::VALUE_REQUIRED, 'Also provide a pac file. Usage : --pac HOST:PORT:PROXY_HOST:PROXY_PORT')
            ->addArgument('binding', InputArgument::OPTIONAL, 'Bind address', '127.0.0.1:8001');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configurationFile = $input->getOption('config');
        if (is_string($configurationFile)) {
            if (file_exists($configurationFile)) {
                $savedOptions = $this->cleanOptionForSave(OptionsHelper::read($configurationFile));
                foreach ($savedOptions as $name => $option) {
                    $input->setOption($name, $input->getOption($name) ?? $option);
                }
            }

            if ((bool) $input->getOption('save')) {
                OptionsHelper::save($configurationFile, $this->cleanOptionForSave($input->getOptions()));
            }
        }

        $translatorBuilder = $this->buildTranslatorBuilder($input);
        /** @phpstan-ignore-next-line */
        $authenticator = AuthenticatorFactory::create((array) $input->getOption('auth'));
        $dataHandlerFactory = new DataHandlerFactory($this->loop, $translatorBuilder, $authenticator, $this->logger);

        $binding = $input->getArgument('binding');
        if (!is_string($binding)) {
            throw new \RuntimeException('Invalid argument binding.');
        }
        $server = new TcpServer($binding, $this->loop);
        $this->logger->info('PROXY Server listening on {binding}', ['binding' => $binding]);

        if ((bool) $input->getOption('ssl')) {
            $context = [];
            if ((bool) $input->getOption('ssl-cert')) {
                $context['local_cert'] = $input->getOption('ssl-cert');
            }
            if ((bool) $input->getOption('ssl-key')) {
                $context['local_pk'] = $input->getOption('ssl-key');
            }
            if ((bool) $input->getOption('ssl-passphrase')) {
                $context['passphrase'] = $input->getOption('passphare');
            }

            $server = new SecureServer($server, $this->loop, $context);
        }
        $server->on('connection', new ConnectionHandler($dataHandlerFactory));

        $pacConfiguration = $input->getOption('pac');
        if (is_string($pacConfiguration)) {
            $this->configurePACServer($pacConfiguration, $translatorBuilder);
        }

        $this->loop->run();

        // Never reached
        return 1;
    }

    private function buildTranslatorBuilder(InputInterface $input): TranslatorBuilder
    {
        $translatorBuilder = TranslatorBuilder::create();
        $translations = (array) $input->getOption('translate');
        foreach ($translations as $translation) {
            [$from, $to] = explode('=', (string) $translation, 2);
            $translatorBuilder->set($from, $to);
        }

        return $translatorBuilder;
    }

    /**
     * @param array<string, array<string>|string|null> $options
     *
     * @return array<string, string>
     */
    private function cleanOptionForSave(array $options): array
    {
        $cleaned = [];
        foreach (['ssl', 'ssl-cert', 'ssl-key', 'ssl-passphrase', 'translate', 'auth'] as $name) {
            if (isset($options[$name])) {
                $cleaned[$name] = $options[$name];
            }
        }

        /* @phpstan-ignore-next-line */
        return $cleaned;
    }

    private function configurePACServer(string $pacConfiguration, TranslatorBuilder $translatorBuilder): void
    {
        $serverFactory = new ServerFactory($this->loop, $this->logger);
        [$binding, $proxyHost] = $this->parsePACConfiguration($pacConfiguration);
        $serverFactory->create($binding, $proxyHost, $translatorBuilder);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function parsePACConfiguration(string $pacConfiguration): array
    {
        if (!(bool) preg_match('|^(.+?:\d+?):(.+?:\d+?)$|', $pacConfiguration, $matches)) {
            throw new \InvalidArgumentException('Bad PAC configuration value : should be BINDING_HOST:BINDING_PORT:EXTERNAL_PROXY_HOST:EXTERNAL_PROXY_PORT');
        }

        return [$matches[1], $matches[2]];
    }
}
