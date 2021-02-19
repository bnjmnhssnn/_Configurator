<?php
namespace Grav\Plugin\IServConfigurator;

use Grav\Common\Config\Config;
use Grav\Common\Session;


class Configurator
{
    protected $steps, $choices, $state;

    public function __construct(Config $config, Session $session)
    {
        $this->configured_steps = $config->get('plugins.configurator.configurator_tree.steps');
        $this->configured_choices = $config->get('plugins.configurator.configurator_choices');

        $step_index = 1;
        if (empty($session->configurator)) {
            $this->state = [
                'steps' => array_map(
                    function($step) use (&$step_index) {
                        $res = [
                            'id' => $step_index,
                            'visible' => ($step_index === 1),
                            'choice' => NULL
                        ];
                        $step_index++;
                        return $res;
                    },
                    $this->configured_steps
                )
            ];
        } else {
            $this->state = $session->configurator;    
        }
    }

    public function getState()
    {
        return $this->state;
    }
}