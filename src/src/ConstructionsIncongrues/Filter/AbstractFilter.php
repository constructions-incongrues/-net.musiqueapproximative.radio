<?php

// TODO : configurable trait?

namespace ConstructionsIncongrues\Filter;

use ConstructionsIncongrues\Entity\Playlist;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractFilter
{
    protected $name = 'abstract';
    protected $parameters = [];

    public function __construct(array $parameters = [])
    {
        $parameters = array_merge(
            ['workingDirectory' => sys_get_temp_dir()],
            $parameters
        );
        $this->configure($parameters);
        $this->makeWorkingDirectory();
    }

    protected function configure($parameters = [])
    {
        $this->setParameters($parameters);
    }

    private function makeWorkingDirectory()
    {
        $fs = new Filesystem();
        $workingDirectory = sprintf('%s/%s', $this->getParameters()['workingDirectory'], $this->getName());
        $fs->mkdir($workingDirectory);
        $this->parameters['workingDirectory'] = $workingDirectory;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Playlist $playlist
     * @return Playlist
     */
    abstract public function filter(Playlist $playlist);
}
