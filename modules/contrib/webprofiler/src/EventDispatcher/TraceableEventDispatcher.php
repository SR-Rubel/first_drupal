<?php

namespace Drupal\webprofiler\EventDispatcher;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Component\EventDispatcher\Event;
use Drupal\webprofiler\Stopwatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\Event as ContractsEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

/**
 * Class TraceableEventDispatcher.
 */
class TraceableEventDispatcher extends ContainerAwareEventDispatcher implements EventDispatcherTraceableInterface {

  /**
   * @var \Drupal\webprofiler\Stopwatch
   *   The stopwatch service.
   */
  protected $stopwatch;

  /**
   * @var array
   */
  protected $calledListeners;

  /**
   * @var array
   */
  protected $notCalledListeners;

  /**
   * {@inheritdoc}
   */
  public function __construct(ContainerInterface $container, array $listeners = []) {
    parent::__construct($container, $listeners);
    $this->notCalledListeners = $listeners;
  }

  /**
   * {@inheritdoc}
   */
  public function addListener($event_name, $listener, $priority = 0) {
    parent::addListener($event_name, $listener, $priority);
    $this->notCalledListeners[$event_name][$priority][] = ['callable' => $listener];
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch($event/*, string $event_name = NULL*/) {
    $event_name = 1 < \func_num_args() ? func_get_arg(1) : NULL;
    if (\is_object($event)) {
      $class_name = get_class($event);
      $event_name = $event_name ?? $class_name;

      $deprecation_message = 'Symfony\Component\EventDispatcher\Event is deprecated in drupal:9.1.0 and will be replaced by Symfony\Contracts\EventDispatcher\Event in drupal:10.0.0. A new Drupal\Component\EventDispatcher\Event class is available to bridge the two versions of the class. See https://www.drupal.org/node/3159012';

      // Trigger a deprecation error if the deprecated Event class is used
      // directly.
      if ($class_name === 'Symfony\Component\EventDispatcher\Event') {
        @trigger_error($deprecation_message, E_USER_DEPRECATED);
      }
      // Also try to trigger deprecation errors when classes are in the Drupal
      // namespace and inherit directly from the deprecated class. If a class is
      // in the Symfony namespace or a different one, we have to assume those
      // will be updated by the dependency itself. Exclude the Drupal Event
      // bridge class as a special case, otherwise it's pointless.
      elseif ($class_name !== 'Drupal\Component\EventDispatcher\Event' && strpos($class_name, 'Drupal') !== FALSE) {
        if (get_parent_class($event) === 'Symfony\Component\EventDispatcher\Event') {
          @trigger_error($deprecation_message, E_USER_DEPRECATED);
        }
      }
    }
    elseif (\is_string($event) && (NULL === $event_name || $event_name instanceof ContractsEvent || $event_name instanceof Event)) {
      @trigger_error('Calling the Symfony\Component\EventDispatcher\EventDispatcherInterface::dispatch() method with a string event name as the first argument is deprecated in drupal:9.1.0, an Event object will be required instead in drupal:10.0.0. See https://www.drupal.org/node/3154407', E_USER_DEPRECATED);
      $swap = $event;
      $event = $event_name ?? new Event();
      $event_name = $swap;
    }
    else {
      throw new \TypeError(sprintf('Argument 1 passed to "%s::dispatch()" must be an object, %s given.', ContractsEventDispatcherInterface::class, \gettype($event)));
    }

    $this->preDispatch($event_name, $event);
    $e = $this->stopwatch->start($event_name, 'section');

    if (isset($this->listeners[$event_name])) {
      // Sort listeners if necessary.
      if (isset($this->unsorted[$event_name])) {
        krsort($this->listeners[$event_name]);
        unset($this->unsorted[$event_name]);
      }

      // Invoke listeners and resolve callables if necessary.
      foreach ($this->listeners[$event_name] as &$definitions) {
        foreach ($definitions as &$definition) {
          if (!isset($definition['callable'])) {
            $definition['callable'] = [
              $this->container->get($definition['service'][0]),
              $definition['service'][1],
            ];
          }
          if (is_array($definition['callable']) && isset($definition['callable'][0]) && $definition['callable'][0] instanceof \Closure) {
            $definition['callable'][0] = $definition['callable'][0]();
          }

          call_user_func($definition['callable'], $event, $event_name, $this);
          if ($event->isPropagationStopped()) {
            return $event;
          }
        }
      }
    }

    if ($e->isStarted()) {
      $e->stop();
    }

    $this->postDispatch($event_name, $event);

    return $event;
  }

  /**
   * {@inheritdoc}
   */
  public function getCalledListeners() {
    return $this->calledListeners;
  }

  /**
   * {@inheritdoc}
   */
  public function getNotCalledListeners() {
    return $this->notCalledListeners;
  }

  /**
   * @param \Drupal\webprofiler\Stopwatch $stopwatch
   */
  public function setStopwatch(Stopwatch $stopwatch) {
    $this->stopwatch = $stopwatch;
  }

  /**
   * Called before dispatching the event.
   *
   * @param string $eventName
   *   The event name.
   * @param \Symfony\Component\EventDispatcher\Event|\Drupal\Component\EventDispatcher\Event $event
   *   The event.
   */
  protected function preDispatch($eventName, $event) {
    switch ($eventName) {
      case KernelEvents::VIEW:
      case KernelEvents::RESPONSE:
        // Stop only if a controller has been executed.
        if ($this->stopwatch->isStarted('controller')) {
          $this->stopwatch->stop('controller');
        }
        break;
    }
  }

  /**
   * Called after dispatching the event.
   *
   * @param string $eventName
   *   The event name.
   * @param \Symfony\Component\EventDispatcher\Event|\Drupal\Component\EventDispatcher\Event $event
   *   The event.
   */
  protected function postDispatch($eventName, $event) {
    switch ($eventName) {
      case KernelEvents::CONTROLLER:
        $this->stopwatch->start('controller', 'section');
        break;

      case KernelEvents::RESPONSE:
        $token = $event->getResponse()->headers->get('X-Debug-Token');
        try {
          $this->stopwatch->stopSection($token);
        } catch (\LogicException $e) {
        }
        break;

      case KernelEvents::TERMINATE:
        // In the special case described in the `preDispatch` method above, the
        // `$token` section does not exist, then closing it throws an exception
        // which must be caught.
        $token = $event->getResponse()->headers->get('X-Debug-Token');
        try {
          $this->stopwatch->stopSection($token);
        } catch (\LogicException $e) {
        }
        break;
    }
  }

  /**
   * @param $definition
   * @param $event_name
   * @param $priority
   */
  private function addCalledListener($definition, $event_name, $priority) {
    if ($this->isClosure($definition['callable'])) {
      $this->calledListeners[$event_name][$priority][] = [
        'class' => 'Closure',
        'method' => '',
      ];
    }
    else {
      $this->calledListeners[$event_name][$priority][] = [
        'class' => get_class($definition['callable'][0]),
        'method' => $definition['callable'][1],
      ];
    }

    foreach ($this->notCalledListeners[$event_name][$priority] as $key => $listener) {
      if (isset($listener['service'])) {
        if ($listener['service'][0] == $definition['service'][0] && $listener['service'][1] == $definition['service'][1]) {
          unset($this->notCalledListeners[$event_name][$priority][$key]);
        }
      }
      else {
        if ($this->isClosure($listener['callable'])) {
          if (is_callable($listener['callable'], TRUE, $listenerCallableName) && is_callable($definition['callable'], TRUE, $definitionCallableName)) {
            if ($listenerCallableName == $definitionCallableName) {
              unset($this->notCalledListeners[$event_name][$priority][$key]);
            }
          }
        }
        else {
          if (get_class($listener['callable'][0]) == get_class($definition['callable'][0]) && $listener['callable'][1] == $definition['callable'][1]) {
            unset($this->notCalledListeners[$event_name][$priority][$key]);
          }
        }
      }

    }
  }

  /**
   *
   */
  private function isClosure($t) {
    return is_object($t) && ($t instanceof \Closure);
  }

}
