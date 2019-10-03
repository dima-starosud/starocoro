<?php
declare(strict_types=1);

namespace Staro\Coro;

use Exception;
use Generator;
use SplQueue;

final class DidNotYieldAValueException extends Exception {
}

const value = '#_value';
const await = Await::class;
const spawn = Spawn::class;
const postpone = Postpone::class;

final class Loop {
    /**
     * @param Generator $generator
     */
    public static function run(Generator $generator) {
        $handlers = new Handlers();
        $streams = new SplQueue();
        $streams->enqueue( new Stream( $generator ) );
        while (!$streams->isEmpty()) {
            /* @var Stream $stream */
            $stream = $streams->dequeue();
            $next = $stream->getNext();
            if (empty( $next ))
                continue;

            list( $key, $value ) = $next;

            $result = is_integer( $key ) ?
                $handlers->getHandler1( $value )->handle( $stream ) :
                $handlers->getHandler2( $key )->handle( $stream, $value );

            foreach ($result as $stream) {
                $streams->enqueue( $stream );
            }
        }
    }
}
