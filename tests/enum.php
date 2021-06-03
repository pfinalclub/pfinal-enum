<?php
/**
 * @author pfinal南丞
 * @date 2021年06月03日 下午3:32
 */

require __DIR__.'/../vendor/autoload.php';

class ErrorCode extends \pf\enum\Enum
{
    /**
     * @msg('非法的TOKEN')
     */
    const VIEW = 'view';
    const EDIT = 'edit';
}

$code = ErrorCode::VIEW();
var_dump(ErrorCode::getMessage($code));exit();