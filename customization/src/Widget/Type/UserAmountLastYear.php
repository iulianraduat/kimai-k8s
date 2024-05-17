<?php

/*
 * Copyright 2024 Iulian Radu <iulian-radu.com>
 */

namespace App\Widget\Type;

use App\Configuration\SystemConfiguration;
use App\Event\UserRevenueStatisticEvent;
use App\Repository\TimesheetRepository;
use App\Widget\WidgetException;
use App\Widget\WidgetInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use DateTime;

final class UserAmountLastYear extends AbstractCounterYear
{
    public function __construct(private TimesheetRepository $repository, SystemConfiguration $systemConfiguration, private EventDispatcherInterface $dispatcher)
    {
        parent::__construct($systemConfiguration);
    }

    public function getOptions(array $options = []): array
    {
        return array_merge([
            'icon' => 'money',
            'color' => WidgetInterface::COLOR_YEAR,
        ], parent::getOptions($options));
    }

    protected function getYearData(\DateTimeInterface $begin, \DateTimeInterface $end, array $options = []): mixed
    {
        $begin = DateTime::createFromInterface($begin)->modify('-1 year');
        $end = DateTime::createFromInterface($end)->modify('-1 year');
        try {
            $data = $this->repository->getRevenue($begin, $end, $this->getUser());

            $event = new UserRevenueStatisticEvent($this->getUser(), $begin, $end);
            foreach ($data as $row) {
                $event->addRevenue($row->getCurrency(), $row->getAmount());
            }
            $this->dispatcher->dispatch($event);

            return $event->getRevenue();
        } catch (\Exception $ex) {
            throw new WidgetException(
                'Failed loading widget data: ' . $ex->getMessage()
            );
        }
    }

    public function getPermissions(): array
    {
        return ['view_rate_own_timesheet'];
    }

    public function getTemplateName(): string
    {
        return 'widget/widget-counter-money.html.twig';
    }

    protected function getFinancialYearTitle(): string
    {
        return 'Amount Last Financial Year';
    }

    public function getTitle(): string
    {
        return 'My revenue last year';
    }

    public function getId(): string
    {
        return 'UserAmountLastYear';
    }
}
