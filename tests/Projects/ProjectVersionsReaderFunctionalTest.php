<?php

declare(strict_types=1);

namespace Doctrine\Website\Tests\Projects;

use Doctrine\Website\Projects\ProjectVersionsReader;
use Doctrine\Website\Repositories\ProjectRepository;
use Doctrine\Website\Tests\TestCase;
use function print_r;

class ProjectVersionsReaderFunctionalTest extends TestCase
{
    public function testGetProjectVersions() : void
    {
        $container = $this->getContainer();

        /** @var ProjectRepository $projectRepository */
        $projectRepository = $container->get(ProjectRepository::class);

        $project = $projectRepository->findOneBySlug('orm');

        $projectVersionReader = $container->get(ProjectVersionsReader::class);

        $versions = $projectVersionReader->getProjectVersions($project);

        print_r($versions);
    }
}
