<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Plugin\IServConfigurator\Configurator;

/**
 * Class IServConfiguratorPlugin
 * @package Grav\Plugin
 */
class IServConfiguratorPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
    * Composer autoload.
    *is
    * @return ClassLoader
    */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    public function onPluginsInitialized(): void
    {
        if ($this->isAdmin()) {
            return;
        }
        $this->enable(
            [
                'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
                'onPageInitialized' => ['onPageInitialized', 0],
            ]   
        );
    }


    public function onPageInitialized()
    {
        $request = $this->grav['request'];
        if($request->getMethod() === 'POST') {
            $configurator = new Configurator($this->grav['config'], $this->grav['session']);
            $configurator->updateState($_POST);
            $new_state = $configurator->getState();
            $session = $this->grav['session'];
            $session->configurator = $new_state;
            header('Content-Type: application/json');
            echo json_encode($new_state);
            exit;
        }
    }


    /**
     * [onTwigTemplatePaths] Add twig paths to plugin templates.
     */
    public function onTwigTemplatePaths()
    {
        $twig = $this->grav['twig'];
        $twig->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * [onTwigSiteVariables] Set all twig variables for generating output.
     */
    public function onTwigSiteVariables()
    {
        $twig = $this->grav['twig'];
        $configurator = new Configurator($this->grav['config'], $this->grav['session']);
        $twig->twig_vars['configurator'] = $configurator->getTwigVars();
    }
}
