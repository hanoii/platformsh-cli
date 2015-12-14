<?php

namespace Platformsh\Cli\Local\DependencyManager;

class Bundler extends DependencyManagerBase
{
    protected $command = 'bundler';

    /**
     * {@inheritdoc}
     */
    public function getInstallHelp()
    {
        return 'See http://bundler.io/ for installation instructions.';
    }

    /**
     * {@inheritdoc}
     */
    public function getBinPaths($prefix)
    {
        return [$prefix . '/bin'];
    }

    /**
     * @param string $path
     *
     * @param array  $dependencies
     */
    public function install($path, array $dependencies)
    {
        $gemFile = $path . '/Gemfile';
        $gemFileContent = $this->formatGemfile($dependencies);
        $current = file_exists($gemFile) ? file_get_contents($gemFile) : false;
        if ($current !== $gemFileContent) {
            file_put_contents($gemFile, $gemFileContent);
            if (file_exists($path . '/Gemfile.lock')) {
                unlink('Gemfile.lock');
            }
        }
        $this->runCommand('bundler install --path=. --binstubs', $path);
    }

    /**
     * @param array $dependencies
     *
     * @return string
     */
    private function formatGemfile(array $dependencies)
    {
        $lines = ["source 'https://rubygems.org'"];
        foreach ($dependencies as $package => $version) {
            if ($version != '*') {
                $lines[] = sprintf("gem '%s', '%s', :require => false", $package, $version);
            } else {
                $lines[] = sprintf("gem '%s', :require => false", $package);
            }
        }

        return implode("\n", $lines);
    }
}
