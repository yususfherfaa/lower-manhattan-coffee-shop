<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Recommendation extends BaseConfig
{
    /** KNN confidence is returned as a percentage on a 0-100 scale. */
    public const LOW_CONFIDENCE_THRESHOLD = 15.0;
}
