<?php

class Loader
{

    public function view()
    {
        return $this->load(Template::class);
    }

    public function controller($name)
    {
        $className = $name . 'Controller';

        return $this->load($className);
    }

    public function model($name)
    {
        $className = $name . 'Model';

        return $this->load($className);
    }

    public function library($name)
    {
        $className = $name . 'Library';

        return $this->load($className);
    }

    private function load($className)
    {
        return Container::get($className);
    }
}
