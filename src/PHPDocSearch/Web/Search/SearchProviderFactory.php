<?php

namespace PHPDocSearch\Web\Search;

use \PHPDocSearch\PDOProvider,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Symbols\BookFactory,
    \PHPDocSearch\Symbols\ClassFactory,
    \PHPDocSearch\Symbols\ClassMemberFactory,
    \PHPDocSearch\Symbols\ConfigOptionFactory,
    \PHPDocSearch\Symbols\ConstantFactory,
    \PHPDocSearch\Symbols\ControlStructureFactory,
    \PHPDocSearch\Symbols\FunctionFactory,
    \PHPDocSearch\Symbols\MagicMethodFactory;

class SearchProviderFactory
{
    /**
     * Create a new SearchProvider instance
     *
     * @param Request $request
     * @return SearchProvider
     */
    public function create(Request $request)
    {
        return new SearchProvider(
            new QueryParser(new QueryFactory),
            new QueryResolver(
                new QueryCache,
                new PDOProvider($request->getConfig()),
                new DataMapper(
                    new BookFactory,
                    new ClassFactory,
                    new ClassMemberFactory,
                    new ConfigOptionFactory,
                    new ConstantFactory,
                    new ControlStructureFactory,
                    new FunctionFactory,
                    new MagicMethodFactory
                )
            ),
            $request
        );
    }
}
