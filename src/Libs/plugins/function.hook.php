<?php

function smarty_function_hook(array $params): string
{
    return Hook::render($params['name'] ?? '');
}
