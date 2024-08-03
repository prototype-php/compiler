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

namespace Prototype\Compiler\Internal\Proto;

use Prototype\Compiler\Internal\Parser\Context;
use Prototype\Compiler\Internal\Parser\Protobuf3BaseVisitor;

/**
 * @internal
 * @psalm-internal Prototype\Compiler
 */
final class ToSchemaConverter extends Protobuf3BaseVisitor
{
    /**
     * {@inheritdoc}
     */
    public function visitProto(Context\ProtoContext $context): Schema
    {
        return new Schema(
            $context->packageStatement(0)?->accept(new PackageNameVisitor()) ?: null,
            array_filter(
                array_map(
                    static fn (Context\OptionStatementContext $context): ?Option => $context->accept(new OptionVisitor()),
                    $context->optionStatement() ?: [],
                ),
                static fn (?Option $option): bool => null !== $option,
            ),
            array_filter(
                array_map(
                    static fn (Context\MessageDefContext $context): ?Message => $context->accept(new MessageVisitor()),
                    array_map(
                        static fn (Context\TopLevelDefContext $context): Context\MessageDefContext => $context->messageDef(),
                        array_filter(
                            $context->topLevelDef() ?: [],
                            static fn (Context\TopLevelDefContext $context): bool => null !== $context->messageDef(),
                        ),
                    ),
                ),
                static fn (?Message $message): bool => null !== $message,
            ),
        );
    }
}
