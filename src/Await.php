<?php


namespace Staro\Coro;


use Generator;

final class Await implements Handler2 {
    /**
     * @param Stream $continuation
     * @param Generator $async
     * @return Stream[]
     * @throws DidNotYieldAValueException
     */
    function handle(Stream $continuation, $async): array {
        return [new Stream( self::handleContinuation( $async, $continuation->destruct() ) )];
    }

    /**
     * @param Generator $async
     * @param Generator $continuation
     * @return Generator
     * @throws DidNotYieldAValueException
     */
    public static function handleContinuation(Generator $async, Generator $continuation) {
        # TODO preserve "advance generator right before handling" rule here
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
