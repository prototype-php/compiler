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

use Prototype\Compiler\Exception\TypeIsNotDefined;
use Prototype\Compiler\Internal\Parser\Context;
use Prototype\Compiler\Internal\Parser\Protobuf3BaseVisitor;
use Typhoon\Type\types;

/**
 * @internal
 * @psalm-internal Prototype\Compiler
 */
final class FieldVisitor extends Protobuf3BaseVisitor
{
    /**
     * {@inheritdoc}
     */
    public function visitField(Context\FieldContext $context): ?Field
    {
        if (null !== ($fieldName = $context->fieldName()?->ident()?->IDENTIFIER()?->getText())) {
            return new Field(
                $fieldName,
                self::tryType($context->type_() ?: throw TypeIsNotDefined::forField($fieldName))->accept(
                    new ToPhpTypeGenerator(),
                ),
                (int)$context->fieldNumber()?->getText() ?: 0,
            );
        }

        return null;
    }

    private static function tryType(Context\Type_Context $context): \Typhoon\Type\Type
    {
        return match (true) {
            default => types::class($context->getText()),
        };
    }
}
