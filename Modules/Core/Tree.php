<?php


namespace Modules\Core;


use Illuminate\Support\Facades\Request;

class Tree {

    /**
     * Contains tree item
     *
     * @var array
     */
    public $items = [];

    /**
     * Contains acl roles
     *
     * @var array
     */
    public $roles = [];

    /**
     * Contains current item route
     *
     * @var string
     */
    public $current;

    /**
     * Contains current item key
     *
     * @var string
     */
    public $currentKey;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->current = Request::url();
    }

    /**
     * Shortcut method for create a Config with a callback.
     * This will allow you to do things like fire an event on creation.
     *
     * @param  callable  $callback Callback to use after the Config creation
     * @return object
     */
    public static function create($callback = null)
    {
        $tree = new Tree();

        if ($callback) {
            $callback($tree);
        }

        return $tree;
    }

    /**
     * Add a Config item to the item stack
     *
     * @param  string  $item
     * @return void
     */
    public function add($item, $type = '')
    {
        $item['children'] = [];
        if ($type == 'acl') {
            $item['name'] = trans($item['name']);

            $this->roles[$item['route']] = $item['key'];
        }

        $children = str_replace('.', '.children.', $item['key']);

        $this->array_set($this->items, $children, $item);
    }

    /**
     * Method to find the active links
     *
     * @param  array  $item
     * @return string|void
     */
    public function getActive($item)
    {
        $url = trim($item['url'], '/');

        if ((strpos($this->current, $url) !== false) || (strpos($this->currentKey, $item['key']) === 0)) {
            return 'active';
        }
    }

    /**
     * @param array            $items
     * @param string           $key
     * @param string|int|float $value
     *
     * @return array
     */
    public function array_set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);
        $count = count($keys);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];

                if ($key != 'children') {
                    $array[$key] = [
                        "key" => "{$key}.all",
                        "name" => ucfirst($key),
                        "route" => null,
                        "sort" => 1,
                        "module" => null
                    ];
                }
            }

            $array = &$array[$key];
        }

        $finalKey = array_shift($keys);

        if (isset($array[$finalKey])) {
            $value = is_array($value) ? $value : [];
            $array[$finalKey] = $this->arrayMerge($array[$finalKey], $value);
        } else {
            $array[$finalKey] = $value;
        }

        return $array;
    }

    protected function arrayMerge(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMerge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
