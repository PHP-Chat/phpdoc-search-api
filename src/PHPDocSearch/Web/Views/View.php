<?php

namespace PHPDocSearch\Web\Views
{
    use \PHPDocSearch\Web\TemplateFetcher,
        \PHPDocSearch\Web\Request,
        \PHPDocSearch\Web\ContentNegotiation\MIMEType;

    abstract class View
    {
        /**
         * @var Request
         */
        protected $request;

        /**
         * @var TemplateFetcher
         */
        protected $templateFetcher;

        /**
         * @var MIMEType
         */
        protected $contentType;

        /**
         * Constructor
         *
         * @param TemplateFetcher $templateFetcher
         * @param Request $request
         * @param MIMEType $contentType
         */
        public function __construct(TemplateFetcher $templateFetcher, Request $request, MIMEType $contentType = null)
        {
            $this->templateFetcher = $templateFetcher;
            $this->request = $request;
            $this->contentType = $contentType;
        }

        /**
         * Output the content of this view
         */
        abstract public function render();
    }
}

// lack of function autoloading sucks
namespace
{
    /**
     * Short alias for htmlspecialchars()
     *
     * @param string $str
     * @return string
     */
    function html($str)
    {
        return htmlspecialchars($str, ENT_COMPAT | ENT_HTML5 | ENT_SUBSTITUTE, 'utf-8');
    }
}
