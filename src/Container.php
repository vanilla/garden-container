<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2016 Vanilla Forums Inc.
 * @license MIT
 */

namespace Garden\Container;

use Interop\Container\ContainerInterface;

/**
 * An inversion of control container.
 */
class Container implements ContainerInterface {
    private $currentRule;
    private $instances;
    private $rules;
    private $factories;

    /**
     * Construct a new instance of the {@link Container} class.
     */
    public function __construct() {
        $this->rules = ['*' => ['inherit' => true, 'constructorArgs' => []]];
        $this->currentRule = &$this->rules['*'];
        $this->instances = [];
        $this->factories = [];
    }

    /**
     * Normalize a container entry ID.
     *
     * @param string $id The ID to normalize.
     * @return string Returns a normalized ID as a string.
     */
    private function normalizeID($id) {
        return ltrim($id, '\\');
    }

    /**
     * Set the current rule to the default rule.
     *
     * @return $this
     */
    public function defaultRule() {
        $this->currentRule = &$this->rules['*'];
        return $this;
    }

    /**
     * Set the current rule.
     *
     * @param string $id The ID of the rule.
     * @return $this
     */
    public function rule($id) {
        $id = $this->normalizeID($id);

        if (!isset($this->rules[$id])) {
            $this->rules[$id] = [];
        }
        $this->currentRule = &$this->rules[$id];
        return $this;
    }

    /**
     * Set the name of the class for the current rule.
     *
     * @param string $value A valid class name.
     * @return $this
     */
    public function setClass($value) {
        $this->currentRule['class'] = $value;
        return $this;
    }

    /**
     * Set whether or not the current rule is shared.
     *
     * @param bool $value Whether or not the current rule is shared.
     * @return $this
     */
    public function setShared($value) {
        $this->currentRule['shared'] = $value;
        return $this;
    }

    /**
     * Set whether or not the current rule extends to subclasses.
     *
     * @param bool $value Pass **true** to have subclasses inherit this rule or **false** otherwise.
     * @return $this
     */
    public function setInherit($value) {
        $this->currentRule['inherit'] = $value;
        return $this;
    }

    /**
     * Set the constructor arguments for the current rule.
     *
     * @param array $args An array of constructor arguments.
     * @return $this
     */
    public function setConstructorArgs(array $args) {
        $this->currentRule['constructorArgs'] = $args;
        return $this;
    }

    /**
     * Set a specific shared instance into the container.
     *
     * When you set an instance into the container then it will always be returned by subsequent retrievals, even if a
     * rule is configured that says that instances should not be shared.
     *
     * @param string $name The name of the container entry.
     * @param mixed $instance This instance.
     * @return $this
     */
    public function setInstance($name, $instance) {
        $this->instances[$this->normalizeID($name)] = $instance;
        return $this;
    }

    /**
     * Add a method call to a rule.
     *
     * @param string $method The name of the method to call.
     * @param array $args The arguments to pass to the method.
     * @return $this
     */
    public function addCall($method, array $args = []) {
        $this->currentRule['calls'][] = [$method, $args];

        return $this;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @param array $args Additional arguments to pass to the constructor.
     *
     * @throws NotFoundException No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function getArgs($id, array $args = []) {
        $id = $this->normalizeID($id);

        // A shared instance just gets returned.
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->factories[$id])) {
            return $this->factories[$id]($args);
        }

        // The factory or instance isn't registered so do that now.
        // This call also caches the instance or factory fo faster access next time.
        return $this->createInstance($id, $args);
    }

    /**
     * Make a rule based on an ID.
     *
     * @param string $nid A normalized ID.
     * @return array Returns an array representing a rule.
     */
    private function makeRule($nid) {
        $rule = isset($this->rules[$nid]) ? $this->rules[$nid] : [];

        if (class_exists($nid)) {
            for ($class = get_parent_class($nid); !empty($class); $class = get_parent_class($class)) {
                // Don't add the rule if it doesn't say to inherit.
                if (!isset($this->rules[$class]) || (isset($this->rules[$class]['inherit']) && !$this->rules[$class]['inherit'])) {
                    break;
                }
                $rule += $this->rules[$class];
            }

            // Add the default rule.
            if (!empty($this->rules['*']['inherit'])) {
                $rule += $this->rules['*'];
            }

            // Add interface calls to the rule.
            $interfaces = class_implements($nid);
            foreach ($interfaces as $interface) {
                if (!empty($this->rules[$interface]['calls'])
                    && (!isset($this->rules[$interface]['inherit']) || $this->rules[$interface]['inherit'] !== false)) {

                    $rule['calls'] = array_merge(
                        isset($rule['calls']) ? $rule['calls'] : [],
                        $this->rules[$interface]['calls']
                    );
                }
            }
        } elseif (!empty($this->rules['*']['inherit'])) {
            // Add the default rule.
            $rule += $this->rules['*'];
        }

        return $rule;
    }

    /**
     * Make a function that creates objects from a rule.
     *
     * @param string $nid The normalized ID of the container item.
     * @param array $rule The resolved rule for the ID.
     * @return \Closure Returns a function that when called will create a new instance of the class.
     * @throws NotFoundException No entry was found for this identifier.
     */
    private function makeFactory($nid, array $rule) {
        $className = empty($rule['class']) ? $nid : $rule['class'];
        if (!class_exists($className)) {
            throw new NotFoundException("Class $className does not exist.", 404);
        }
        $class = new \ReflectionClass($className);
        $constructor = $class->getConstructor();

        if ($constructor && $constructor->getNumberOfParameters() > 0) {
            $constructorArgs = $this->makeDefaultArgs($constructor, $rule['constructorArgs'], $rule);

            $factory = function($args) use ($class, $constructorArgs) {
                return $class->newInstanceArgs($this->resolveArgs($constructorArgs, $args));
            };
        } else {
            $factory = function() use ($className) {
                return new $className;
            };
        }

        // Add calls to the factory.
        if (!empty($rule['calls'])) {
            $calls = [];

            // Generate the calls array.
            foreach ($rule['calls'] as $call) {
                list($methodName, $args) = $call;
                $method = $class->getMethod($methodName);
                $calls[] = [$methodName, $this->makeDefaultArgs($method, $args, $rule)];
            }

            // Wrap the factory in one that makes the calls.
            $factory = function($args) use ($factory, $calls) {
                $instance = $factory($args);

                foreach ($calls as $call) {
                    call_user_func_array(
                        [$instance, $call[0]],
                        $this->resolveArgs($call[1], [], $instance)
                    );
                }

                return $instance;
            };
        }

        return $factory;
    }

    /**
     * Create a shared instance of a class from a rule.
     *
     * This method has the side effect of adding the new instance to the internal instances array of this object.
     *
     * @param string $nid The normalized ID of the container item.
     * @param array $rule The resolved rule for the ID.
     * @param array $args Additional arguments passed during creation.
     * @return object Returns the the new instance.
     * @throws NotFoundException Throws an exception if the class does not exist.
     */
    private function createSharedInstance($nid, array $rule, array $args) {
        $className = empty($rule['class']) ? $nid : $rule['class'];
        if (!class_exists($className)) {
            throw new NotFoundException("Class $className does not exist.", 404);
        }
        $class = new \ReflectionClass($className);
        $constructor = $class->getConstructor();

        if ($constructor && $constructor->getNumberOfParameters() > 0) {
            $constructorArgs = $this->resolveArgs(
                $this->makeDefaultArgs($constructor, $rule['constructorArgs'], $rule),
                $args
            );

            // Instantiate the object first so that this instance can be used for cyclic dependencies.
            $this->instances[$nid] = $instance = $class->newInstanceWithoutConstructor();
            $constructor->invokeArgs($instance, $constructorArgs);
        } else {
            $this->instances[$nid] = $instance = new $class->name;
        }

        // Call subsequent calls on the new object.
        if (!empty($rule['calls'])) {
            foreach ($rule['calls'] as $call) {
                list($methodName, $args) = $call;
                $method = $class->getMethod($methodName);

                $args = $this->resolveArgs(
                    $this->makeDefaultArgs($method, $args, $rule),
                    [],
                    $instance
                );

                $method->invokeArgs($instance, $args);
            }
        }

        return $instance;
    }

    /**
     * Make an array of default arguments for a given function.
     *
     * @param \ReflectionFunctionAbstract $function The function to make the arguments for.
     * @param array $ruleArgs An array of default arguments specifically for the function.
     * @param array $rule The entire rule.
     * @return array Returns an array in the form `name => defaultValue`.
     */
    private function makeDefaultArgs(\ReflectionFunctionAbstract $function, array $ruleArgs, array $rule = []) {
        $ruleArgs = array_change_key_case($ruleArgs);
        $result = [];

        $pos = 0;
        foreach ($function->getParameters() as $i => $param) {
            $name = strtolower($param->name);

            if (array_key_exists($name, $ruleArgs)) {
                $value = $ruleArgs[$name];
            } elseif ($param->getClass()) {
                $value = new DefaultReference($this->normalizeID($param->getClass()->getName()));
            } elseif (array_key_exists($pos, $ruleArgs)) {
                $value = $ruleArgs[$pos];
                $pos++;
            } elseif ($param->isDefaultValueAvailable()) {
                $value = $param->getDefaultValue();
            } else {
                $value = null;
            }

            $result[$name] = $value;
        }

        return $result;
    }

    /**
     * Replace an array of default args with called args.
     *
     * @param array $defaultArgs The default arguments from {@link Container::makeDefaultArgs()}.
     * @param array $args The arguments passed into a creation.
     * @param mixed $instance An object instance if the arguments are being resolved on an already constructed object.
     * @return array Returns an array suitable to be applied to a function call.
     */
    private function resolveArgs(array $defaultArgs, array $args, $instance = null) {
        $args = array_change_key_case($args);

        $pos = 0;
        foreach ($defaultArgs as $name => &$arg) {
            if (array_key_exists($name, $args)) {
                // This is a named arg and should be used.
                $value = $args[$name];
            } elseif (isset($args[$pos]) && (!($arg instanceof DefaultReference) || is_a($args[$pos], $arg->getName()))) {
                // There is an arg at this position and it's the same type as the default arg or the default arg is typeless.
                $value = $args[$pos];
                $pos++;
            } else {
                // There is no passed arg, so use the default arg.
                $value = $arg;
            }

            if ($value instanceof ReferenceInterface) {
                $value = $value->resolve($this, $instance);
            }
            $arg = $value;
        }

        return $defaultArgs;
    }

    /**
     * Create an instance of a container item.
     *
     * This method either creates a new instance or returns an already created shared instance.
     *
     * @param string $nid The normalized ID of the container item.
     * @param array $args Additional arguments to pass to the constructor.
     * @return object Returns an object instance.
     */
    private function createInstance($nid, array $args) {
        $rule = $this->makeRule($nid);

        // Cache the instance or its factory for future use.
        if (empty($rule['shared'])) {
            $factory = $this->makeFactory($nid, $rule);
            $instance = $factory($args);
            $this->factories[$nid] = $factory;
        } else {
            $instance = $this->createSharedInstance($nid, $rule, $args);
        }
        return $instance;
    }

    /**
     * Call a callback with argument injection.
     *
     * @param callable $callback The callback to call.
     * @param array $args Additional arguments to pass to the callback.
     * @return mixed Returns the result of the callback.
     * @throws ContainerException Throws an exception if the callback cannot be understood.
     */
    public function call(callable $callback, array $args = []) {
        $instance = null;
        if (is_string($callback) || $callback instanceof \Closure) {
            $function = new \ReflectionFunction($callback);
        } elseif (is_array($callback)) {
            $function = new \ReflectionMethod($callback[0], $callback[1]);

            if (is_object($callback[0])) {
                $instance = $callback[0];
            }
        } else {
            throw new ContainerException("Could not understand callback.", 500);
        }

        $args = $this->resolveArgs($this->makeDefaultArgs($function, $args), [], $instance);

        return call_user_func_array($callback, $args);
    }

    /**
     * Returns true if the container can return an entry for the given identifier. Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id) {
        $id = $this->normalizeID($id);

        return isset($this->instances[$id]) || isset($this->rules[$id]) || class_exists($id);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id) {
        return $this->getArgs($id);
    }
}
