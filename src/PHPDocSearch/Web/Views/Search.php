<?php

namespace PHPDocSearch\Web\Views;

use \PHPDocSearch\Web\TemplateFetcher,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\ContentNegotiation\ContentType,
    \PHPDocSearch\Web\Search\SearchProvider,
    \PHPDocSearch\Symbols\Book,
    \PHPDocSearch\Symbols\GlobalClass,
    \PHPDocSearch\Symbols\ClassConstant,
    \PHPDocSearch\Symbols\ClassMethod,
    \PHPDocSearch\Symbols\ClassProperty,
    \PHPDocSearch\Symbols\ConfigOption,
    \PHPDocSearch\Symbols\GlobalConstant,
    \PHPDocSearch\Symbols\ControlStructure,
    \PHPDocSearch\Symbols\GlobalFunction,
    \PHPDocSearch\Symbols\MagicMethod;

class Search implements View
{
    private $templateFetcher;

    private $request;

    private $contentType;

    private $searchProvider;

    public function __construct(TemplateFetcher $templateFetcher, Request $request, ContentType $contentType, SearchProvider $searchProvider)
    {
        $this->templateFetcher = $templateFetcher;
        $this->request = $request;
        $this->contentType = $contentType;
        $this->searchProvider = $searchProvider;
    }

    private function renderJSON()
    {
        $results = $this->searchProvider->getResult();

        $data = (object) [
            'count' => count($results)
        ];

        foreach ($results as $result) {
            switch (true) {
                case $result instanceof Book:
                    if (!isset($data->books)) {
                        $data->books = [];
                    }

                    $data->books[] = $result;
                    break;

                case $result instanceof GlobalClass:
                    if (!isset($data->classes)) {
                        $data->classes = [];
                    }

                    $data->classes[] = $result;
                    break;

                case $result instanceof ClassConstant:
                    if (!isset($data->class_constants)) {
                        $data->class_constants = [];
                    }

                    $data->class_constants[] = $result;
                    break;

                case $result instanceof ClassMethod:
                    if (!isset($data->class_methods)) {
                        $data->class_methods = [];
                    }

                    $data->class_methods[] = $result;
                    break;

                case $result instanceof ClassProperty:
                    if (!isset($data->class_properties)) {
                        $data->class_properties = [];
                    }

                    $data->class_properties[] = $result;
                    break;

                case $result instanceof ConfigOption:
                    if (!isset($data->config_options)) {
                        $data->config_options = [];
                    }

                    $data->config_options[] = $result;
                    break;

                case $result instanceof GlobalConstant:
                    if (!isset($data->constants)) {
                        $data->constants = [];
                    }

                    $data->constants[] = $result;
                    break;

                case $result instanceof ControlStructure:
                    if (!isset($data->control_structures)) {
                        $data->control_structures = [];
                    }

                    $data->control_structures[] = $result;
                    break;

                case $result instanceof GlobalFunction:
                    if (!isset($data->functions)) {
                        $data->functions = [];
                    }

                    $data->functions[] = $result;
                    break;

                case $result instanceof MagicMethod:
                    if (!isset($data->magic_methods)) {
                        $data->magic_methods = [];
                    }

                    $data->magic_methods[] = $result;
                    break;
            }
        }

        return json_encode($data);
    }

    public function render()
    {
        header("Content-Type: $this->contentType");

        if ($this->contentType->getSubType() === 'json') {
            return $this->renderJSON();
        } else if ($this->contentType->getSubType() === 'xml') {
            return $this->renderXML();
        }
    }
}
