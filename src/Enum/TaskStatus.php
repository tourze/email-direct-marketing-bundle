<?php

namespace EmailDirectMarketingBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 任务状态
 */
enum TaskStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case WAITING = 'waiting';
    case SENDING = 'sending';
    case FINISHED = 'finished';

    public function getLabel(): string
    {
        return match ($this) {
            self::WAITING => '等待发送',
            self::SENDING => '发送中',
            self::FINISHED => '已完成',
        };
    }
}
