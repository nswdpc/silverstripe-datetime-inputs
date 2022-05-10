<fieldset class="CompositeField {$extraClass}" id="{$HolderID}">
    <legend>{$Title.XML}</legend>
    <% if $FormatExample %><p class="description example">{$FormatExample.XML}</p><% end_if %>
    <% if $FieldWarning %><p class="description warning">{$FieldWarning.XML}</p><% end_if %>
    <% if $RightTitle %><p class="right helper">{$RightTitle}</p><% end_if %>
    <% if $Message %><p class="message {$MessageType.XML}">{$Message}</p><% end_if %>
    <% if $Description %><p class="description">{$Description.XML}</p ><% end_if %>
    <div class="inputs">
    {$Field}
    </div>
</fieldset>
