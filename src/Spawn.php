<?php


namespace Staro\Coro;


final class Spawn implements Handler2 {
    /**
     * @param Stream $current
     * @param mixed $value
     * @return Stream[]
     */
    function handle(Stream $current, $value): array {
        return [$current, new Stream( $value )];
    }
}
