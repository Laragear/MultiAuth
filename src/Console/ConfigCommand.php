<?php

namespace Laragear\MultiAuth\Console;

use Composer\Command\BaseCommand;
use InvalidArgumentException;
use Laragear\MultiAuth\Factory as MultiAuthFactory;
use Laragear\MultiAuth\Support\Arr;
use Laragear\MultiAuth\Support\Composer;
use Laragear\MultiAuth\Support\File;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends BaseCommand
{
    /**
     * Create a new Config Command instance.
     */
    public function __construct(
        protected Arr $arr,
        protected Composer $composerHelper,
        protected File $file,
        protected MultiAuthFactory $factory,
        ?string $name = null,
        ?callable $code = null,
    ) {
        parent::__construct($name, $code);
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->setName('multi-auth:config')
            ->setDescription('Configures per-package authentication credentials in auth.json')
            ->setDefinition([
                new InputArgument('package', InputArgument::REQUIRED, 'The package name (e.g. vendor/package)'),
                new InputArgument('type', InputArgument::REQUIRED,
                    'The authentication type (e.g. license, bearer, query)'),
                new InputArgument('credentials', InputArgument::IS_ARRAY,
                    'The credential values. Format depends on type.'),
                new InputOption('global', 'g', InputOption::VALUE_NONE,
                    'Apply to the global auth.json instead of the project auth.json'),
            ])
            ->setHelp(
                <<<EOT
The <info>multi-auth:config</info> command sets per-package authentication in <info>auth.json</info>.

Example:
  <info>composer multi-auth:config laragear/pkg license ABC-123</info>
  <info>composer multi-auth:config laragear/pkg query auth license:\$TOKEN</info>
  <info>composer multi-auth:config laragear/pkg http-basic username password</info>
EOT,
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $package = $input->getArgument('package');
        $type = $input->getArgument('type');
        $isGlobal = $input->getOption('global');

        $configFile = $isGlobal
            ? ($this->composerHelper->createConfig()->get('home').'/auth.json')
            : 'auth.json';

        $file = $this->factory->makeJsonFile($configFile);

        $data = $file->exists() ? $file->read() : [];

        $credentialsData = $this->parseCredentials($package, $type, $input->getArgument('credentials'));

        if (!isset($data[$type])) {
            $data[$type] = [];
        }

        $data[$type][$package] = $credentialsData;

        // Use JsonManipulator to preserve the formatting if the file exists.
        if ($file->exists()) {
            $manipulator = $this->factory->makeJsonManipulator($this->file->getContents($configFile));

            // Add array notation for nested manipulation
            $manipulator->addMainKey($type, $data[$type]);

            $this->file->putContents($configFile, $manipulator->getContents());
        } else {
            $file->write($data);
        }

        $this->getIO()->write(
            "<info>Configured multi-auth for $package in ".($isGlobal ? 'global ' : '')."auth.json</info>",
        );

        return 0;
    }

    /**
     * Sets the correct credentials scheme for the given type and arguments.
     */
    protected function parseCredentials(string $package, string $type, array $args): array|string
    {
        return match ($type) {
            'http-basic' => $this->parseHttpBasic($package, $args),
            'query' => $this->parseQueryArgs($args),
            default => $args[0] ?? '',
        };
    }

    /**
     * Parse the arguments for the credentials for an HTTP Basic authentication.
     *
     * @return array{username: string, password: string}
     */
    private function parseHttpBasic(string $package, array $args): array
    {
        if ($this->arr->count($args) < 2) {
            throw new InvalidArgumentException(
                "Package [$package] http-basic requires username and password arguments.",
            );
        }

        return [
            'username' => $args[0],
            'password' => $args[1],
        ];
    }

    /**
     * Parse the arguments for the credentials inside an URL Query.
     *
     * @return string[]
     */
    private function parseQueryArgs(array $args): array
    {
        $queryData = [];

        foreach ($this->arr->chunk($args, 2) as $pair) {
            [$key, $value] = [$pair[0], $pair[1] ?? ''];

            $queryData[$key][] = $value;
        }

        return $queryData;
    }
}
