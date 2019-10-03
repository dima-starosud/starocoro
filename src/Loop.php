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

final class Loop {
    /**
     * @param Generator $generator
     * @throws UnsupportedYieldException
     * @throws DidNotYieldAValueException
     */
    public static function run(Generator $generator) {
        $streams = new SplQueue();
        $streams->enqueue( new Stream( $generator ) );
        while (!$streams->isEmpty()) {
            /* @var Stream $stream */
            $stream = $streams->dequeue();
            $next = $stream->getNext();
            if (empty( $next ))
                continue;

            list( $key, $value ) = $next;

            if ($key === await) {
                $generator = static::handleContinuation( $value, $stream->destruct() );
                $streams->enqueue( new Stream( $generator ) );
            } elseif ($key === spawn) {
                $streams->enqueue( $stream );
                $streams->enqueue( new Stream( $value ) );
            } elseif (is_integer( $key ) and $value === postpone) {
                $streams->enqueue( $stream );
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
