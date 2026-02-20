{assign var=flex value=$flex|default:false}

<a href="{$tsConfig.url}/{$tsPage}/{$link|default:''}" class="sidebar-link block rounded p-2 hover:bg-gray-100 dark:hover:bg-gray-800{if $flex} flex justify-between{/if}">{$label|default:''}{if $flex} {include "dashboard/aside/Badge.tpl" total=$total}{/if}</a>