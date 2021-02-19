<?php
namespace Grav\Plugin\IServConfigurator;

use Grav\Common\Config\Config;
use Grav\Common\Session;


class Configurator
{
    protected $configured_steps, $configured_choices, $form_target, $state;

    public function __construct(Config $config, Session $session)
    {
        $this->configured_steps = $config->get('plugins.i-serv-configurator.configurator_tree.steps');
        $this->configured_choices = $config->get('plugins.i-serv-configurator.configurator_choices');
        $this->form_target = $config->get('plugins.i-serv-configurator.configurator_form_target');

        $step_index = 1;
        if (empty($session->configurator)) {
            $this->state = [
                'steps' => array_map(
                    function($step) use (&$step_index) {
                        $res = [
                            'id' => $step_index,
                            'visible' => ($step_index === 1)                        
                        ];
                        if($step_index < count($this->configured_steps)) {
                            $res['choice'] = NULL;
                        }
                        $step_index++;
                        return $res;
                    },
                    $this->configured_steps
                ),
                'ready' => false
            ];
        } else {
            $this->state = $session->configurator;    
        }
    }

    public function updateState(array $post_vars) : void
    {
        $step_index = 1;
        $this->state['steps'] = array_map(

            function($step) use (&$step_index, $post_vars) {
            
                if($post_vars['action'] === 'confirm') {

                    if ($step_index < count($this->configured_steps)) { // Beim letzten (Summary-) Step bleibt choices leer
                        if($step_index === (int) $post_vars['step_id']) {
                            $step['choice'] = (int) $post_vars['choice'];
                        } elseif ($step_index > (int) $post_vars['step_id']) {
                            $step['choice'] = NULL;   
                        }
                    }
                    $step['visible'] = ($step_index === ((int)$post_vars['step_id'] + 1));

                } elseif ($post_vars['action'] === 'back') {

                    if ($step_index < count($this->configured_steps)) { // Beim letzten (Summary-) Step bleibt choices leer
                        if ($step_index >= (int) $post_vars['step_id'] - 1) {
                            $step['choice'] = NULL;   
                        }
                    }
                    $step['visible'] = ($step_index === ((int)$post_vars['step_id'] - 1));
                }
                $step_index++;
                return $step;
            },
            $this->state['steps']
        );
        $this->state['ready'] = ($post_vars['action'] === 'confirm' && (int) $post_vars['step_id'] === count($this->configured_steps));
    }

    public function getState() : array
    {
        return $this->state;
    }

    public function ready() : bool  
    {
        return $this->state['ready'] ?? false;
    }

    public function getTwigVars() : array
    {
        $state_by_step_id = array_combine(
            array_column($this->state['steps'], 'id'), 
            $this->state['steps']
        );
        $choices_by_id = array_combine(
            array_column($this->configured_choices, 'id'), 
            $this->configured_choices
        );
        $step_index = 1;
        $processed_steps = array_map(
            function($step) use ($choices_by_id, $state_by_step_id, &$step_index) {
                $step['id'] = $step_index;
                $step['visible'] = $state_by_step_id[$step_index]['visible'];
                if(!empty($step['choices'])) {
                    $step['choices'] = array_map(
                        function($choice) use ($choices_by_id) {
                            return $choices_by_id[$choice['id']];
                        },
                        $step['choices']
                    );
                }
                $step_index++;
                return $step;
            },
            $this->configured_steps        
        );

        return [
            'form_target' => $this->form_target,
            'steps' => $processed_steps,
            'ready' => $this->state['ready'],
            'debug_state' => '<br><br><pre style="font-size: 10px; line-height: 11px">' . print_r($this->state, true) . '</pre>'
        ];
    }
}