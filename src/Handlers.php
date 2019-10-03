<?php
declare(strict_types=1);

namespace Staro\Coro;


final class Handlers {
    private $handlers = [];

    function getHandler2(string $class): Handler2 {
        return $this->get( $class );
    }

    function getHandler1(string $class): Handler1 {
        return $this->get( $class );
    }

    private function get(string $class) {
        return $this->handlers[$class] ?? ($this->handlers[$class] = new $class);
    }
}
