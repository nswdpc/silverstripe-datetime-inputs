<div id="{$HolderID}" class="CompositeField {$extraClass}">
    <label class="left">{$Title.XML}</label>
    <% if $FormatExample %><p class="description format-example">{$FormatExample}</p><% end_if %>
    <% if $FieldWarning %><p class="description field-warning">{$FieldWarning}</p><% end_if %>
    <% if $Message %><p class="message {$MessageType.XML}">{$Message}</p><% end_if %>
    <% if $Description %><p class="description">{$Description}</p><% end_if %>
    <div class="inputs">
    {$Field}
    </div>
    <% if $RightTitle %><p class="right helper">{$RightTitle}</p><% end_if %>
</div>
