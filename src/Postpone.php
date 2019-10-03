<?php


namespace Staro\Coro;


final class Postpone implements Handler1 {
    /**
     * @param Stream $current
     * @return Stream[]
     */
    function handle(Stream $current): array {
        return [$current];
    }
}
