<?php

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIntoBuilding;
use Building\Domain\DomainEvent\UserCheckedOutOfBuilding;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $checkedInUsers;

    public static function new($name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        // Don't let a user check in more than once
        if (in_array($username, $this->checkedInUsers, true)) {
            throw new \InvalidArgumentException(sprintf(
                'User "%s" is already checked in',
                $username
            ));
        }

        $this->recordThat(UserCheckedIntoBuilding::occur(
            $this->id(),
            [
                'username' => $username
            ]
        ));
    }

    public function checkOutUser(string $username)
    {
        // Don't let a user check in more than once
        if (! in_array($username, $this->checkedInUsers, true)) {
            throw new \InvalidArgumentException(sprintf(
                'User "%s" is not checked in',
                $username
            ));
        }


        $this->recordThat(UserCheckedOutOfBuilding::occur(
            $this->id(),
            [
                'username' => $username
            ]
        ));
    }

    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = $event->uuid();
        $this->name = $event->name();
    }

    public function whenUserCheckedIntoBuilding(UserCheckedIntoBuilding $event)
    {
        $username = $event->username();

        $this->checkedInUsers[] = $username;

        $this->checkedInUsers = array_unique($this->checkedInUsers);
    }

    public function whenuserCheckedOutOfBuilding(UserCheckedOutOfBuilding $event)
    {
        $checkedOutUser = $event->username();
        $this->checkedInUsers = \array_filter(
            $this->checkedInUsers,
            function($username) use ($checkedOutUser) {
                return $username !== $checkedOutUser;
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
