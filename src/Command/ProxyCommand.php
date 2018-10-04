<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Command;

use Paxal\Phproxy\Proxy\Authenticator\AuthenticatorFactory;
use Paxal\Phproxy\Proxy\ConnectionHandler;
use Paxal\Phproxy\Proxy\DataHandlerFactory;
use Paxal\Phproxy\Translator\TranslatorBuilder;
use Paxal\Phproxy\Translator\TranslatorInterface;
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

    public function __construct(LoopInterface $loop)
    {
        parent::__construct();
        $this->loop = $loop;
    }

    protected function configure()
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
            ->addArgument('binding', InputArgument::OPTIONAL, 'Bind address', '127.0.0.1:8001');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationFile = $input->getOption('config');
        if (is_string($configurationFile)) {
            if (file_exists($configurationFile)) {
                $savedOptions = $this->cleanOptionForSave(OptionsHelper::read($configurationFile));
                foreach ($savedOptions as $name => $option) {
                    $input->setOption($name, $input->getOption($name) ?: $option);
                }
            }

            if ($input->getOption('save')) {
                OptionsHelper::save($configurationFile, $this->cleanOptionForSave($input->getOptions()));
            }
        }

        $translator = $this->buildTranslator($input);
        $authenticator = AuthenticatorFactory::create((array) $input->getOption('auth'));
        $dataHandlerFactory = new DataHandlerFactory($this->loop, $translator, $authenticator);

        $binding = $input->getArgument('binding');
        if (!is_string($binding)) {
            throw new \RuntimeException('Invalid argument binding.');
        }
        $server = new TcpServer($binding, $this->loop);

        if ($input->getOption('ssl')) {
            $context = [];
            if ($input->getOption('ssl-cert')) {
                $context['local_cert'] = $input->getOption('ssl-cert');
            }
            if ($input->getOption('ssl-key')) {
                $context['local_pk'] = $input->getOption('ssl-key');
            }
            if ($input->getOption('ssl-passphrase')) {
                $context['passphrase'] = $input->getOption('passphare');
            }

            $server = new SecureServer($server, $this->loop, $context);
        }
        $server->on('connection', new ConnectionHandler($dataHandlerFactory));
        $this->loop->run();
    }

    private function buildTranslator(InputInterface $input): TranslatorInterface
    {
        $translatorBuilder = TranslatorBuilder::create();
        $translations = (array) $input->getOption('translate');
        foreach ($translations as $translation) {
            [$from, $to] = explode('=', (string) $translation, 2);
            $translatorBuilder->set($from, $to);
        }

        return $translatorBuilder->build();
    }

    private function cleanOptionForSave(array $options): array
    {
        $cleaned = [];
        foreach (['ssl', 'ssl-cert', 'ssl-key', 'ssl-passphrase', 'translate', 'auth'] as $name) {
            if (isset($options[$name])) {
                $cleaned[$name] = $options[$name];
            }
        }

        return $cleaned;
    }
}
