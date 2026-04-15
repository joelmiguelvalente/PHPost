{if $tsUser->is_admod}
<style>
.widget-logs {
   border: 1px solid #ddd;
   border-radius: 8px;
   overflow: hidden;
   margin-bottom: 12px;
   background: #f9f9f9;
   font-family: 'JetBrains Mono', monospace;
}
.widget-logs.has-logs {
   border-color: #ff453a55;
   box-shadow: 0 0 10px #ff453a18;
}
.widget-logs.no-logs {
   border-color: #30d15844;
}
/* Header */
.wl-header {
   display: flex;
   align-items: center;
   gap: 7px;
   padding: 8px 12px;
   background: #efefef;
   border-bottom: 1px solid #ddd;
}
.widget-logs.has-logs .wl-header {
   background: #fff0ef;
   border-bottom-color: #ff453a33;
}
.widget-logs.no-logs .wl-header {
   background: #f0faf1;
   border-bottom-color: #30d15833;
}
.wl-icon {
   font-size: 13px;
}
.wl-title {
   font-size: 11px;
   font-weight: 700;
   letter-spacing: .08em;
   text-transform: uppercase;
   color: #333;
   flex: 1;
}
.wl-badge {
   background: #ff453a;
   color: #fff;
   font-size: 10px;
   font-weight: 700;
   padding: 1px 6px;
   border-radius: 999px;
   min-width: 18px;
   text-align: center;
}
.wl-body {
   padding: 10px 12px;
   display: flex;
   flex-direction: column;
   gap: 6px;
}

.wl-row {
   display: flex;
   align-items: center;
   justify-content: space-between;
   gap: 8px;
}
.wl-label {
   font-size: 10px;
   color: #3e3a3a;
   text-transform: uppercase;
   letter-spacing: .06em;
   white-space: nowrap;
}
.wl-value {
   font-size: 11px;
   color: #444;
   text-align: right;
}
.wl-value.error {
   color: #ff453a;
   font-weight: 700;
}
.wl-level {
   font-size: 10px;
   font-weight: 700;
   padding: 2px 8px;
   border-radius: 5px;
   letter-spacing: .06em;
}
.wl-ok {
   font-size: 11px;
   color: #28a745;
   text-align: center;
   padding: 4px 0;
}
.wl-btn {
   display: block;
   margin-top: 4px;
   padding: 7px 10px;
   background: #d3352c;
   color: #fff!important;
   font-size: 11px;
   font-weight: 700;
   text-align: center;
   text-decoration: none;
   border-radius: 6px;
   letter-spacing: .04em;
   transition: background .15s;
}
.wl-btn:hover {
   background: #C4271E;
   color: #fff;
}
.wl-btn.secondary {
   background: #e8e8e8;
   color: #333!important;
   border: 1px solid #ccc;
}
.wl-btn.secondary:hover {
   background: #ddd;
   color: #444;
}
[data-skin="dark"] .widget-logs {
   border-color: #2a2a2a;
   background: #111113;
}
[data-skin="dark"] .widget-logs.has-logs {
   border-color: #ff453a44;
   box-shadow: 0 0 12px #ff453a18;
}
[data-skin="dark"] .widget-logs.no-logs {
   border-color: #30d15833;
}
[data-skin="dark"] .wl-header {
   background: #1a1a1c;
   border-bottom-color: #2a2a2a;
}
[data-skin="dark"] .widget-logs.has-logs .wl-header {
   background: #1e1010;
   border-bottom-color: #ff453a22;
}

[data-skin="dark"] .widget-logs.no-logs .wl-header {
   background: #0e1a0f;
   border-bottom-color: #30d15822;
}
[data-skin="dark"] .wl-title {
   color: #ccc;
}
[data-skin="dark"] .wl-label {
   color: #555;
}
[data-skin="dark"] .wl-value {
   color: #aaa;
}
[data-skin="dark"] .wl-ok {
   color: #30d158;
}
[data-skin="dark"] .wl-btn.secondary {
   background: #1e1e22;
   color: #888;
   border-color: #333;
}
[data-skin="dark"] .wl-btn.secondary:hover {
   background: #2a2a30;
   color: #aaa;
}
</style>
<div class="widget-logs {if $logWidget.hasToday}has-logs{else}no-logs{/if}">
   <div class="wl-header">
      <span class="wl-icon">{if $logWidget.hasToday}&#9888;{else}&#10003;{/if}</span>
      <span class="wl-title">System Logs</span>
      {if $logWidget.hasToday and $logWidget.totalErrors > 0}
         <span class="wl-badge">{$logWidget.totalErrors}</span>
      {/if}
   </div>
   <div class="wl-body">
      {if $logWidget.hasToday}
         {if $logWidget.worstLevel}
            <div class="wl-row">
               <span class="wl-label">Nivel</span>
               <span class="wl-level" style="color:{$logWidget.worstColor};background:{$logWidget.worstBg}">
                  {$logWidget.worstIcon} {$logWidget.worstLevel}
               </span>
            </div>
         {/if}
         {if $logWidget.totalErrors > 0}
            <div class="wl-row">
               <span class="wl-label">Errores hoy</span>
               <span class="wl-value error">{$logWidget.totalErrors}</span>
            </div>
         {/if}
         {if $logWidget.lastDatetime}
            <div class="wl-row">
               <span class="wl-label">Ultimo</span>
               <span class="wl-value">{$logWidget.lastDatetime}</span>
            </div>
         {/if}
         <a href="{$tsConfig.url}{$logWidget.url}" target="_blank" class="wl-btn">Ver logs</a>
      {else}
         <p class="wl-ok">Sin actividad hoy</p>
         <a href="{$tsConfig.url}{$logWidget.url}" target="_blank" class="wl-btn secondary">Ver historial</a>
      {/if}
   </div>
</div>
{/if}
