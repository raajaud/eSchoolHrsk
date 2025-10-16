<?php

namespace App\Repositories\UserCharge;

use App\Models\UserCharge;
use App\Repositories\Saas\SaaSRepository;

class FeesRepository extends SaaSRepository implements UserChargeInterface {
    public function __construct(UserCharge $model) {
        parent::__construct($model);
    }
}
