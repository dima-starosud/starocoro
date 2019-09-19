<?php
declare(strict_types=1);

namespace Staro\Coro;

use PHPUnit\Framework\TestCase;


final class ExecutorTest extends TestCase {
    private static function simpleCoro1($log) {
        $log( 'coro1: enter' );
        yield value => 42;
        $log( 'coro1: exit' );
    }

    private static function simpleCoro2($log) {
        $log( 'coro2: enter' );
        $x1 = yield await => static::simpleCoro1( $log );
        $log( 'coro2: $x1 ' . $x1 );
        $log( 'coro2: exit' );
    }

    public function testSimpleCoro(): void {
        $logs = [];
        $log = function ($data) use (&$logs) {
            $logs[] = $data;
        };

        Executor::execute( static::simpleCoro2( $log ) );

        $this->assertEquals( $logs, [
            'coro2: enter',
            'coro1: enter',
            'coro1: exit',
            'coro2: $x1 42',
            'coro2: exit',
        ] );
    }

    private static function concurrentWorker($name, $log) {
        for ($i = 0; $i < 4; ++$i) {
            yield postpone;
            $log( "$name: $i" );
        }
    }

    private static function concurrentMain($log) {
        yield spawn => static::concurrentWorker( 'first', $log );
        yield spawn => static::concurrentWorker( 'second', $log );
    }

    public function testConcurrent(): void {
        $logs = [];
        $log = function ($data) use (&$logs) {
            $logs[] = $data;
        };

        Executor::execute( static::concurrentMain( $log ) );

        $this->assertEquals( $logs, [
            'first: 0',
            'second: 0',
            'first: 1',
            'second: 1',
            'first: 2',
            'second: 2',
            'first: 3',
            'second: 3',
        ] );
    }
}
