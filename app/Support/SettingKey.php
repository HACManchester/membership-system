<?php

namespace BB\Support;

/**
 * The settings the app knows about. Rows in the settings table whose key isn't
 * listed here are ignored.
 */
enum SettingKey: string
{
    case GeneralInductionCode = 'general_induction_code';
}
