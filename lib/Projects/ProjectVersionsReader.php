<?php

declare(strict_types=1);

namespace Doctrine\Website\Projects;

use Doctrine\Website\Model\Project;
use function array_keys;
use function array_map;
use function explode;
use function file_get_contents;
use function json_decode;
use function ltrim;
use function print_r;
use function shell_exec;
use function sprintf;
use function substr;

class ProjectVersionsReader
{
    private const PACKAGIST_API_URL = 'https://repo.packagist.org/p/%s.json';

    /** @var string */
    private $projectsPath;

    public function __construct(string $projectsPath)
    {
        $this->projectsPath = $projectsPath;
    }

    public function getProjectVersions(Project $project) : array
    {
        $composerPackageName = $project->getComposerPackageName();

        $url = sprintf(self::PACKAGIST_API_URL, $composerPackageName);

        $json = file_get_contents($url);

        $data = json_decode($json, true);

        $versions = $data['packages'][$composerPackageName];

        $versionNames = array_keys($versions);

        $versions = [];

        foreach ($versionNames as $versionName) {
            if (substr($versionName, 0, 4) === 'dev-') {
                continue;
            }

            $versionSlug = $this->getVersionSlug($versionName);

            if (isset($versions[$versionSlug])) {
                continue;
            }

            $versions[$versionSlug] = [
                'name' => $versionSlug,
                'slug' => $versionSlug,
                'branchName' => $this->getBranchName($project, $versionSlug),
            ];
        }

        print_r($versions);
        exit;
    }

    private function getVersionSlug(string $versionName) : string
    {
        $versionSlug = ltrim($versionName, 'v');

        $parts = explode('.', $versionSlug);

        return $parts[0] . '.' . $parts[1];
    }

    private function getBranchName(Project $project, string $versionSlug) : string
    {
        $repositoryPath = $project->getProjectRepositoryPath($this->projectsPath);

        $check = [
            $versionSlug,
            sprintf('%s.x', $versionSlug),
        ];

        foreach ($check as $branchName) {
            $command = sprintf(
                'cd %s && git branch -a',
                $repositoryPath,
                $branchName
            );

            $output = shell_exec($command);

            $lines = array_map('trim', explode("\n", $output));

            foreach ($lines as $line) {
                if ($line === 'remotes/origin/' . $branchName) {
                    return $branchName;
                }
            }
        }

        throw new RuntimeException(sprintf(
            'Could not guess branch name for project %s and version %s',
            $project->getSlug(),
            $versionSlug
        ));
    }
}
