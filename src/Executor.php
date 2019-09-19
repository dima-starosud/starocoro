<?php
declare(strict_types=1);

namespace Staro\Coro;

use Exception;
use Generator;
use SplQueue;

final class DidNotYieldAValueException extends Exception {
}


final class UnsupportedYieldException extends Exception {
}

const value = 'value';
const await = 'await';
const spawn = 'spawn';
const postpone = 'postpone';

final class Executor {
    /**
     * @param Generator $generator
     * @throws UnsupportedYieldException
     * @throws DidNotYieldAValueException
     */
    public static function execute(Generator $generator) {
        $generators = new SplQueue();
        $generators->enqueue( $generator );
        while (!$generators->isEmpty()) {
            $generator = $generators->dequeue();
            if (!$generator->valid())
                continue;

            $key = $generator->key();
            $value = $generator->current();
            if ($key === await) {
                $generator = static::handleContinuation( $value, $generator );
                $generators->enqueue( $generator );
            } elseif ($key === spawn) {
                $generators->enqueue( $generator );
                $generators->enqueue( $value );
                $generator->next();
            } elseif (is_integer( $key ) and $value === postpone) {
                $generators->enqueue( $generator );
                $generator->next();
            } else {
                throw new UnsupportedYieldException( "Unsupported: $key => $value" );
            }
        }
    }

    /**
     * @param Generator $async
     * @param Generator $continuation
     * @return Generator
     * @throws DidNotYieldAValueException
     */
    public static function handleContinuation(Generator $async, Generator $continuation) {
        while (true) {
            if (!$async->valid())
                throw new DidNotYieldAValueException();

            $key = $async->key();
            $value = $async->current();
            $async->next();
            if ($key !== value) {
                yield $key => $value;
            } else {
                $continuation->send( $value );
                break;
            }
        }

        yield spawn => $continuation;
        yield spawn => $async;
    }
}
