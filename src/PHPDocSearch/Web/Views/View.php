<?php

namespace PHPDocSearch\Web\Views
{
    interface View
    {
        public function render();
    }
}

// lack of function autoloading sucks
namespace
{
    function html($str)
    {
        return htmlspecialchars($str, ENT_COMPAT | ENT_HTML5 | ENT_SUBSTITUTE, 'utf-8');
    }
}
