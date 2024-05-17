<?php

/*
 * Copyright 2024 Iulian Radu <iulian-radu.com>
 */

namespace App\Widget\Type;

use App\Repository\TimesheetRepository;
use App\Widget\WidgetException;
use App\Widget\WidgetInterface;

final class DurationLastMonth extends AbstractCounterDuration
{
    public function __construct(private TimesheetRepository $repository)
    {
    }

    public function getOptions(array $options = []): array
    {
        return array_merge(['color' => WidgetInterface::COLOR_MONTH], parent::getOptions($options));
    }

    public function getData(array $options = []): mixed
    {
        try {
            return $this->repository->getDurationForTimeRange(
                $this->createDate('first day of last month 00:00:00'),
                $this->createDate('last day of last month 23:59:59'),
                null
            );
        } catch (\Exception $ex) {
            throw new WidgetException(
                'Failed loading widget data: ' . $ex->getMessage()
            );
        }
    }

    public function getPermissions(): array
    {
        return ['view_other_timesheet'];
    }

    public function getTitle(): string
    {
        return 'Working hours last month';
    }

    public function getId(): string
    {
        return 'DurationLastMonth';
    }
}
