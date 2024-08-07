<?php

/**
 * MIT License
 * Copyright (c) 2024 kafkiansky.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Prototype\Compiler\Console;

use Prototype\Compiler\Compiler;
use Prototype\Compiler\Output\FileWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Prototype\Compiler\Internal;

/**
 * @api
 */
#[AsCommand(
    name: 'compile',
    description: 'Compiles proto files into PHP code',
)]
final class CompileCommand extends Command
{
    public function __construct(
        private readonly string $cwd,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->addArgument('paths', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Paths with proto files')
            ->addOption('imports', 'i', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Import paths.')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'The directory where to save the generated PHP code.')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Optional PHP namespace for generated code.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var non-empty-list<non-empty-string> $paths */
        $paths = $input->getArgument('paths');

        /** @var non-empty-string $outputDir */
        $outputDir = $this->cwd.\DIRECTORY_SEPARATOR.($input->getOption('output') ?: '');

        $files = iterator_to_array(Internal\locateProtoFiles($paths));

        $progress = new ProgressBar($output, \count($files));

        $compiler = Compiler::build(new FileWriter($outputDir));

        foreach ($files as $file) {
            $compiler->compile($file);
            $progress->advance();
        }

        $progress->finish();
        $io->success(\sprintf('All files were compiled successfully into the directory "%s".', $outputDir));

        return self::SUCCESS;
    }
}
