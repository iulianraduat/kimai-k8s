<?php

/*
 * Copyright 2024 Iulian Radu <iulian-radu.com>
 */

namespace App\Widget\Type;

use App\Configuration\SystemConfiguration;
use App\Repository\TimesheetRepository;
use App\Widget\WidgetException;
use App\Widget\WidgetInterface;
use DateTime;

final class DurationLastYear extends AbstractCounterYear
{
    public function __construct(private TimesheetRepository $repository, SystemConfiguration $systemConfiguration)
    {
        parent::__construct($systemConfiguration);
    }

    public function getOptions(array $options = []): array
    {
        return array_merge([
            'icon' => 'duration',
            'color' => WidgetInterface::COLOR_YEAR,
        ], parent::getOptions($options));
    }

    protected function getYearData(\DateTimeInterface $begin, \DateTimeInterface $end, array $options = []): mixed
    {
        $begin = DateTime::createFromInterface($begin)->modify('-1 year');
        $end = DateTime::createFromInterface($end)->modify('-1 year');
        try {
            return $this->repository->getDurationForTimeRange($begin, $end, null);
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

    public function getTemplateName(): string
    {
        return 'widget/widget-counter-duration.html.twig';
    }

    protected function getFinancialYearTitle(): string
    {
        return 'Duration Last Financial Year';
    }

    public function getTitle(): string
    {
        return 'Working hours last year';
    }

    public function getId(): string
    {
        return 'DurationLastYear';
    }
}
