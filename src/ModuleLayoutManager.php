<?php

namespace ModuleLayout;

use Illuminate\Support\Manager;
use Activity\Factory\Factory;

class ModuleLayoutManager extends Manager implements Factory
{
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }
    
    protected function createArticleModuleDriver()
    {
        return $this->buildProvider(ArticleModule::class);
    }
    
    public function buildProvider($provider)
    {
        return new $provider();
    }

    /**
     * Get the default driver name.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No Activity driver was specified.');
    }
}
