<?php

namespace PHPDocSearch\Web;

use PHPDocSearch\Web\Views;

class ViewFetcher
{
    public function fetch($name, Request $request)
    {
        // NB: I don't like this. But it will do for now.
        $className = __NAMESPACE__ . '\\Views\\' . $name;

        if (!class_exists($name)) {
            throw new \InvalidArgumentException('The requested view ' . $name . ' does not exist');
        }

        $additionalArgs = array_merge([new TemplateFetcher], array_slice(func_get_args(), 1));

        return (new \ReflectionClass($className))->newInstanceArgs($additionalArgs);
    }
}
