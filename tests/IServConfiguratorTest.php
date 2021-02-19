<?php
use PHPUnit\Framework\TestCase;
use Grav\Plugin\IServConfigurator\Configurator;
use Symfony\Component\Yaml\Yaml;


final class IServConfiguratorTest extends TestCase
{
    public function testInitialStateWhenSessionEmpty() : void
    {
        $config_mock = $this->getConfigMock();
        $session_mock = $this->getSessionMock();
        $configurator = new Configurator($config_mock, $session_mock);
        $expected_state = [
            'steps' => [
                ['id' => 1, 'visible' => true, 'choice' => NULL],
                ['id' => 2, 'visible' => false, 'choice' => NULL],
                ['id' => 3, 'visible' => false, 'choice' => NULL],
                ['id' => 4, 'visible' => false, 'choice' => NULL],
                ['id' => 5, 'visible' => false, 'choice' => NULL]
            ]
        ];
        $this->assertEquals($expected_state, $configurator->getState());
    }

    public function testRestoreStateFromSession() : void
    {
        $config_mock = $this->getConfigMock();
        $session_mock = $this->getSessionMock();
        $session_mock->configurator = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => true, 'choice' => NULL],
                ['id' => 3, 'visible' => false, 'choice' => NULL],
                ['id' => 4, 'visible' => false, 'choice' => NULL],
                ['id' => 5, 'visible' => false, 'choice' => NULL]
            ]
        ];
        $configurator = new Configurator($config_mock, $session_mock);
        $expected_state = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => true, 'choice' => NULL],
                ['id' => 3, 'visible' => false, 'choice' => NULL],
                ['id' => 4, 'visible' => false, 'choice' => NULL],
                ['id' => 5, 'visible' => false, 'choice' => NULL]
            ]
        ];
        $this->assertEquals($expected_state, $configurator->getState());
    }

    public function testTwigVarsOutput() : void
    {
        $config_mock = $this->getConfigMock();
        $session_mock = $this->getSessionMock();
        $configurator = new Configurator($config_mock, $session_mock);
        // Vorerst nur testen, ob der erste Step korrekt erzeugt wird, später ausführlicher
        $expected = [
            'id' => 1,
            'visible' => true,
            'title' => 'Schultyp wählen',
            'type' => 'radio',
            'choices' => [
                ['id' => 1, 'name' => 'Grund- oder Förderschule', 'type' => 'school_type'],
                ['id' => 2, 'name' => 'Weiterführende Schule', 'type' => 'school_type'],
                ['id' => 3, 'name' => 'Berufsschule', 'type' => 'school_type'],
            ]
        ];
        $this->assertEquals($expected, $configurator->getTwigVars()['steps'][0]);
    }

    protected function getConfigMock()
    {
        $mock = $this->getMockBuilder('Grav\Common\Config\Config')->setMethods(['get'])->getMock();
        $mock
            ->method('get')
            ->will(
                $this->returnCallBack(
                    function($path) {
                        switch($path) {
                            case 'plugins.i-serv-configurator.configurator_tree.steps':
                                return Yaml::parse(file_get_contents(__DIR__ . '/configurator_tree.mock.yaml'));
                            case 'plugins.i-serv-configurator.configurator_choices':
                                return Yaml::parse(file_get_contents(__DIR__ . '/configurator_choices.mock.yaml'));   
                        }    
                    }
                )    
            );
        return $mock;
    }

    protected function getSessionMock()
    {
        return $this->getMockBuilder('Grav\Common\Session')->getMock();
    }

}