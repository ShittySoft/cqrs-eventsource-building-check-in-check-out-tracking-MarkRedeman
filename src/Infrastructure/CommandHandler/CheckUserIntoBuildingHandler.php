<?php

declare(strict_types=1);

namespace Building\Infrastructure\CommandHandler;

use Building\Domain\Aggregate\Building;
use Building\Domain\Command\CheckUserIntoBuilding;
use Building\Domain\Repository\BuildingRepositoryInterface;
use Building\Infrastructure\Repository\BuildingRepository;
use Rhumsaa\Uuid\Uuid;

final class CheckUserIntoBuildingHandler
{
    /**
     * @var BuildingRepository
     */
    private $repository;

    public function __construct(BuildingRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CheckUserIntoBuilding $command)
    {
        $building = $this->repository->get(
            Uuid::fromString(
                $command->buildingId()
            )
        );

        $building->checkInUser($command->username());

        $this->repository->add($building);
    }
}
