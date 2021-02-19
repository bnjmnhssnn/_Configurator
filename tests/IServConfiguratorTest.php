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
                ['id' => 5, 'visible' => false, 'choice' => NULL],
                ['id' => 6, 'visible' => false]
            ],
            'ready' => false
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
                ['id' => 5, 'visible' => false, 'choice' => NULL],
                ['id' => 6, 'visible' => false]
            ],
            'ready' => false
        ];
        $configurator = new Configurator($config_mock, $session_mock);
        $expected_state = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => true, 'choice' => NULL],
                ['id' => 3, 'visible' => false, 'choice' => NULL],
                ['id' => 4, 'visible' => false, 'choice' => NULL],
                ['id' => 5, 'visible' => false, 'choice' => NULL],
                ['id' => 6, 'visible' => false]
            ],
            'ready' => false
        ];
        $this->assertEquals($expected_state, $configurator->getState());
    }

    public function testStateUpdateConfirm() : void
    {
        $config_mock = $this->getConfigMock();
        $session_mock = $this->getSessionMock();
        $configurator = new Configurator($config_mock, $session_mock);

        // User bestätigt Step 1
        $post_vars = [
            'step_id' => 1,
            'choice' => 1,
            'action' => 'confirm'
        ];
        $expected_state_after_update = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => true, 'choice' => NULL],
                ['id' => 3, 'visible' => false, 'choice' => NULL],
                ['id' => 4, 'visible' => false, 'choice' => NULL],
                ['id' => 5, 'visible' => false, 'choice' => NULL],
                ['id' => 6, 'visible' => false]
            ],
            'ready' => false
        ];
        $configurator->updateState($post_vars);
        $this->assertEquals($expected_state_after_update, $configurator->getState());   
    }

    
    public function testStateUpdateBack() : void
    {
        $config_mock = $this->getConfigMock();
        $session_mock = $this->getSessionMock();

        // Step 1 und 2 sind bereits bestätigt und in der Session gespeichert,
        // Step 3 ist also aktiv
        $session_mock->configurator = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => false, 'choice' => 4],
                ['id' => 3, 'visible' => true, 'choice' => NULL],
                ['id' => 4, 'visible' => false, 'choice' => NULL],
                ['id' => 5, 'visible' => false, 'choice' => NULL],
                ['id' => 6, 'visible' => false]
            ],
            'ready' => false
        ];
        $configurator = new Configurator($config_mock, $session_mock);

        // User klickt "Zurück" Button in Step 3
        $post_vars = [
            'step_id' => 3,
            'action' => 'back'
        ];
        $expected_state_after_update = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => true, 'choice' => NULL],
                ['id' => 3, 'visible' => false, 'choice' => NULL],
                ['id' => 4, 'visible' => false, 'choice' => NULL],
                ['id' => 5, 'visible' => false, 'choice' => NULL],
                ['id' => 6, 'visible' => false]
            ],
            'ready' => false
        ];
        $configurator->updateState($post_vars);
        $this->assertEquals($expected_state_after_update, $configurator->getState());   
    }


    public function testReadyStateAfterLastStep() : void
    {
        $config_mock = $this->getConfigMock();
        $session_mock = $this->getSessionMock();

        $session_mock->configurator = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => false, 'choice' => 2],
                ['id' => 3, 'visible' => false, 'choice' => 3],
                ['id' => 4, 'visible' => false, 'choice' => 4],
                ['id' => 5, 'visible' => false, 'choice' => 5],
                ['id' => 6, 'visible' => true]
            ],
            'ready' => false
        ];
        $configurator = new Configurator($config_mock, $session_mock);
        $this->assertFalse($configurator->ready());   
        // User bestätigt finalen Schritt 6
        $post_vars = [
            'step_id' => 6,
            'action' => 'confirm'        
        ];
        $expected_state_after_update = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => false, 'choice' => 2],
                ['id' => 3, 'visible' => false, 'choice' => 3],
                ['id' => 4, 'visible' => false, 'choice' => 4],
                ['id' => 5, 'visible' => false, 'choice' => 5],
                ['id' => 6, 'visible' => false]
            ],
            'ready' => true
        ];
        $configurator->updateState($post_vars);
        $this->assertEquals($expected_state_after_update, $configurator->getState());
        $this->assertTrue($configurator->ready());   
    }


    public function testTwigVarsOutput() : void
    {
        $config_mock = $this->getConfigMock();
        $session_mock = $this->getSessionMock();
        $session_mock->configurator = [
            'steps' => [
                ['id' => 1, 'visible' => false, 'choice' => 1],
                ['id' => 2, 'visible' => false, 'choice' => 4],
                // Diese Wahlmöglichkeit kostet und muss 
                // in der Zusammenfassung auftauchen -->
                ['id' => 3, 'visible' => false, 'choice' => 7], 
                ['id' => 4, 'visible' => true, 'choice' => NULL],
                ['id' => 5, 'visible' => false, 'choice' => NULL],
                ['id' => 6, 'visible' => false]
            ],
            'ready' => false
        ];
        $configurator = new Configurator($config_mock, $session_mock);
        $twig_vars = $configurator->getTwigVars();
        // Vorerst nur testen, ob der erste Step korrekt erzeugt wird, später ausführlicher
        $expected_step = [
            'id' => 1,
            'visible' => false,
            'title' => 'Schultyp wählen',
            'type' => 'radio',
            'choices' => [
                ['id' => 1, 'name' => 'Grund- oder Förderschule', 'type' => 'school_type'],
                ['id' => 2, 'name' => 'Weiterführende Schule', 'type' => 'school_type'],
                ['id' => 3, 'name' => 'Berufsschule', 'type' => 'school_type'],
            ]
        ];
        $this->assertEquals($expected_step, $twig_vars['steps'][0]);
        $expected_summary = [
            'items' => [
                ['name' => 'Portal-M', 'price_line' => 4595]
            ],
            'price_total' => 4595
        ];
        $this->assertEquals($expected_summary, $twig_vars['summary']);
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