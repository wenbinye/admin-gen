<?php
namespace AdminGen;

use PhalconX\Test\TestCase;

/**
 * TestCase for Inflect
 */
class InflectTest extends TestCase
{
    public function testSingularize()
    {
        $this->assertEquals(Inflect::singularize('customers'), 'customer');
    }
}
