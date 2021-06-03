<?php
/**
 * @author pfinal南丞
 * @date 2021年06月03日 下午3:09
 */

namespace pf\enum;

abstract class Enum
{
    public mixed $value;
    protected mixed $key;
    protected static array $cache = [];

    protected static array $instances = [];

    /**
     * Enum constructor.
     * @param $value
     */
    public function __construct($value)
    {
        if ($value instanceof static) {
            $value = $value->getValue();
        }
        $this->key = static::assertValidValueReturningKey($value);
        $this->value = $value;
    }

    /**
     *
     */
    public function __wakeup() :void
    {
        if ($this->key === null) {
            $this->key = static::search($this->value);
        }
    }

    /**
     * @param $value
     * @return static
     */
    public static function from($value): static
    {
        $key = static::assertValidValueReturningKey($value);

        return self::__callStatic($key, []);
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function __toString() :string
    {
        return (string)$this->value;
    }

    /**
     * @param null $variable
     * @return bool
     */
    final public function equals($variable = null): bool
    {
        return $variable instanceof self
            && $this->getValue() === $variable->getValue()
            && static::class === \get_class($variable);
    }

    /**
     * @return int[]|string[]
     */
    public static function keys() :array
    {
        return \array_keys(static::toArray());
    }

    /**
     * @return array
     */
    public static function values(): array
    {
        $values = array();

        foreach (static::toArray() as $key => $value) {
            $values[$key] = new static($value);
        }

        return $values;
    }

    public static function toArray()
    {
        $class = static::class;
        if (!isset(static::$cache[$class])) {
            $reflection = new \ReflectionClass($class);
            static::$cache[$class] = $reflection->getConstants();
        }
        return static::$cache[$class];
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isValid($value): bool
    {
        return \in_array($value, static::toArray(), true);
    }

    /**
     * @param $value
     */
    public static function assertValidValue($value)
    {
        self::assertValidValueReturningKey($value);
    }

    /**
     * @param $value
     * @return false|int|string
     */
    private static function assertValidValueReturningKey($value): bool|int|string
    {
        if (false === ($key = static::search($value))) {
            throw new \UnexpectedValueException("Value '$value' is not part of the enum " . static::class);
        }
        return $key;
    }

    /**
     * @param $key
     * @return bool
     */
    public static function isValidKey($key): bool
    {
        $array = static::toArray();
        return isset($array[$key]) || \array_key_exists($key, $array);
    }

    /**
     * @param $value
     * @return false|int|string
     */
    public static function search($value): bool|int|string
    {
        return \array_search($value, static::toArray(), true);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|static
     */
    public static function __callStatic($name, $arguments)
    {
        $class = static::class;
        if (!isset(self::$instances[$class][$name])) {
            $array = static::toArray();
            if (!isset($array[$name]) && !\array_key_exists($name, $array)) {
                $message = "No static method or enum constant '$name' in class " . static::class;
                throw new \BadMethodCallException($message);
            }
            return self::$instances[$class][$name] = new static($array[$name]);
        }
        return clone self::$instances[$class][$name];
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->getValue();
    }

    /**
     * @param $code
     * @return mixed|null
     * @throws \ReflectionException
     */
    public static function getMessage($code)
    {
        $class = new \ReflectionClass($code);
        try {
            $doc = $class->getReflectionConstant($code->getKey())->getDocComment();
            $pattern = "/\@msg\('(.*?)'\)/U";
            if (preg_match($pattern, $doc, $result)) {
                if (isset($result[1])) {
                    return $result[1];
                }
            }
            return null;
        }catch (\ReflectionException $e) {
            throw new \ReflectionException($e->getMessage());
        }
    }
}