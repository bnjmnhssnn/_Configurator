<?php
use PHPUnit\Framework\TestCase;
use Grav\Plugin\IServConfigurator\Configurator;

final class IServConfiguratorTest extends TestCase
{
    public function testDummy()
    {
        $configurator = new Configurator();
        $this->assertTrue(true);
    }

}