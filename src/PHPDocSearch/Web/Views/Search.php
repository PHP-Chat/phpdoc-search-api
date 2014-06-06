<?php

namespace PHPDocSearch\Web\Views;

use \PHPDocSearch\Web\TemplateFetcher,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\ContentNegotiation\MIMEType,
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

class Search extends View
{
    /**
     * @var SearchProvider
     */
    private $searchProvider;

    /**
     * Constructor
     *
     * @param TemplateFetcher $templateFetcher
     * @param Request $request
     * @param MIMEType $contentType
     * @param SearchProvider $searchProvider
     */
    public function __construct(TemplateFetcher $templateFetcher, Request $request, MIMEType $contentType, SearchProvider $searchProvider)
    {
        parent::__construct($templateFetcher, $request, $contentType);
        $this->searchProvider = $searchProvider;
    }

    /**
     * Render this view in JSON format
     */
    private function renderJSON()
    {
        $results = $this->searchProvider->getResult($this->request->getArg('q'));

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

        echo json_encode($data);
    }

    /**
     * Render this view in XML format
     */
    private function renderXML()
    {
        // todo
    }

    /**
     * Output the content of this view
     */
    public function render()
    {
        header("Content-Type: {$this->contentType}");

        if ($this->contentType->getSubType() === 'json') {
            $this->renderJSON();
        } else if ($this->contentType->getSubType() === 'xml') {
            $this->renderXML();
        }
    }
}
