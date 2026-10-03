<?php

declare(strict_types=1);

namespace Config;

use App\Services\MemberService;
use App\Services\PaymentService;
use App\Services\PointsService;
use App\Services\ReservationService;
use CodeIgniter\Config\BaseService;
use CodeIgniter\Shield\Models\UserModel;

class Services extends BaseService
{
    public static function points(bool $getShared = true): PointsService
    {
        if ($getShared) {
            return static::getSharedInstance('points');
        }

        return new PointsService(db_connect());
    }

    public static function members(bool $getShared = true): MemberService
    {
        if ($getShared) {
            return static::getSharedInstance('members');
        }

        return new MemberService(db_connect(), new UserModel());
    }

    public static function payments(bool $getShared = true): PaymentService
    {
        if ($getShared) {
            return static::getSharedInstance('payments');
        }

        return new PaymentService(db_connect(), static::points());
    }

    public static function reservations(bool $getShared = true): ReservationService
    {
        if ($getShared) {
            return static::getSharedInstance('reservations');
        }

        return new ReservationService(db_connect(), static::points());
    }
}
