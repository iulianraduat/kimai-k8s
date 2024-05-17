<?php

/*
 * Copyright 2024 Iulian Radu <iulian-radu.com>
 */

namespace App\Widget\Type;

use App\Widget\WidgetInterface;

final class UserAmountLastMonth extends AbstractUserRevenuePeriod
{
    public function getOptions(array $options = []): array
    {
        return array_merge(['color' => WidgetInterface::COLOR_MONTH], parent::getOptions($options));
    }

    public function getData(array $options = []): mixed
    {
        return $this->getRevenue(
            $this->createDate('first day of last month 00:00:00'),
            $this->createDate('last day of last month 23:59:59'),
            $options
        );
    }

    public function getTitle(): string
    {
        return 'My revenue last month';
    }

    public function getId(): string
    {
        return 'UserAmountLastMonth';
    }
}
