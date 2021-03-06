<?php

namespace Platformsh\Cli\SiteAlias;

use Platformsh\Client\Model\Project;
use Symfony\Component\Yaml\Yaml;

class DrushYaml extends DrushAlias
{
    /**
     * {@inheritdoc}
     */
    protected function getFilename($groupName)
    {
        return $this->drush->getSiteAliasDir() . '/' . $groupName . '.alias.yml';
    }

    /**
     * {@inheritdoc}
     */
    protected function formatAliases(array $aliases)
    {
        return Yaml::dump($aliases, 5, 2);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExistingAliases($currentGroup, $previousGroup = null)
    {
        $aliases = parent::getExistingAliases($currentGroup, $previousGroup);
        if (empty($aliases)) {
            foreach (array_filter([$currentGroup, $previousGroup]) as $groupName) {
                $filename = $this->getFilename($groupName);
                if (file_exists($filename) && ($content = file_get_contents($filename))) {
                    $aliases = array_merge($aliases, (array) Yaml::parse($content));
                }
            }
        }

        return $aliases;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalize(array $aliases)
    {
        return $this->swapKeys($aliases, [
            'remote-host' => 'host',
            'remote-user' => 'user',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHeader(Project $project)
    {
        return <<<EOT
# Drush aliases for the {$this->config->get('service.name')} project "{$project->title}".
# This file is auto-generated by the {$this->config->get('application.name')}.
#
# WARNING
# This file may be regenerated at any time.
# - User-defined aliases will be preserved.
# - Aliases for active environments (including any custom additions) will be preserved.
# - Aliases for deleted or inactive environments will be deleted.
# - All other information will be deleted.
EOT;
    }
}
