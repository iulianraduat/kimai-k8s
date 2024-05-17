<?php

/*
 * Copyright 2024 Iulian Radu <iulian-radu.com>
 */

namespace App\Widget\Type;

use App\Repository\Query\TimesheetStatisticQuery;
use App\Entity\User;
use App\Timesheet\DateTimeFactory;
use App\Timesheet\TimesheetStatisticService;
use App\Widget\WidgetException;
use App\Widget\WidgetInterface;
use App\Model\Statistic\StatisticDate;
use DateTime;

final class WorkingYearTimeChart extends AbstractWidget
{
    public function __construct(
        private TimesheetStatisticService $statisticService
    ) {
    }

    public function getWidth(): int
    {
        return WidgetInterface::WIDTH_FULL;
    }

    public function getHeight(): int
    {
        return WidgetInterface::HEIGHT_MAXIMUM;
    }

    public function getPermissions(): array
    {
        return ['view_own_timesheet'];
    }

    public function getTitle(): string
    {
        return 'Working Year Time Chart';
    }

    public function getTemplateName(): string
    {
        return 'widget/widget-workingyeartimechart.html.twig';
    }

    public function getId(): string
    {
        return 'WorkingYearTimeChartWidget';
    }

    public function setUser(User $user): void
    {
        parent::setUser($user);
        $now = new DateTime('now', new \DateTimeZone($user->getTimezone()));
        $this->setOption('year', $now->format('o'));
        $this->setOption('month', $now->format('n'));
    }

    public function getOptions(array $options = []): array
    {
        $options = parent::getOptions($options);

        if (!\array_key_exists('type', $options) || !\in_array($options['type'], ['bar', 'line'])) {
            $options['type'] = 'bar';
        }

        if (!\array_key_exists('year', $options)) {
            $options['year'] = (new DateTime('now'))->format('o');
            $options['month'] = (new DateTime('now'))->format('n');
        }

        return $options;
    }

    public function getData(array $options = []): mixed
    {
        $options = $this->getOptions($options);
        $user = $this->getUser();

        $dateTimeFactory = DateTimeFactory::createByUser($user);

        $year = $options['year'];
        if (\is_string($year)) {
            $year = (int) $year;
        } elseif (!\is_int($year)) {
            throw new WidgetException('Invalid year given');
        }

        $month = $options['month'];
        if (\is_string($month)) {
            $month = (int) $month;
        } elseif (!\is_int($month)) {
            throw new WidgetException('Invalid month given');
        }

        $yearBegin = ($dateTimeFactory->createDateTime())->setDate($year - 1, $month, 1)->setTime(0, 0, 0);
        $yearEnd = $dateTimeFactory->createDateTime('23:59:59');
        $query = new TimesheetStatisticQuery($yearBegin, $yearEnd, [$user]);
        // \App\Model\MonthlyStatistic
        $stats = $this->statisticService->getMonthlyStats($query)[0];

        $statsMonths = [];

        $years = $stats->getYears();
        foreach ($years as $year) {
            $yearMonthsData = $stats->getYear($year);
            foreach ($yearMonthsData as $month => $monthData) {
                // App\Model\Statistic\StatisticDate
                $stat = array(
                    'year' => $year,
                    'month' => $month,
                    'billableDuration' => $monthData->getBillableDuration(),
                    'totalRate' => $monthData->getBillableRate()
                );
                array_push($statsMonths, $stat);
            }
        }

        return [
            'stats' => $statsMonths,
            'year' => $year,
            'month' => $options['month'],
        ];
    }
}
