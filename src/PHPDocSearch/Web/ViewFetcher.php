<?php

namespace PHPDocSearch\Web;

use PHPDocSearch\Web\Views\View;

class ViewFetcher
{
    /**
     * Create an instance of the request view
     *
     * @param string $name
     * @param Request $request
     * @return View
     * @throws \InvalidArgumentException
     */
    public function fetch($name, Request $request)
    {
        // NB: I don't like this. But it will do for now.
        $className = sprintf('%s\\Views\\%s', __NAMESPACE__, $name);

        if (!class_exists($className)) {
            throw new \InvalidArgumentException('The requested view ' . $name . ' does not exist');
        }

        $additionalArgs = array_merge([new TemplateFetcher], array_slice(func_get_args(), 1));

        return (new \ReflectionClass($className))->newInstanceArgs($additionalArgs);
    }
}
