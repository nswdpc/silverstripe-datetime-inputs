<div id="$HolderID" class="form-group field<% if $extraClass %> $extraClass<% end_if %>">

    <% if $Title %>
        <label for="$ID" id="title-$ID" class="form__field-label">{$Title.XML}</label>
    <% end_if %>

    <div id="$ID" <% include SilverStripe/Forms/AriaAttributes %> class="form__fieldgroup form__field-holder<% if not $Title %> form__field-holder--no-label<% end_if %><% if $Zebra %> form__fieldgroup-zebra<% end_if %>">

        <% if $FormatExample %><p class="form__field-description form-text example">{$FormatExample}</p><% end_if %>

        <% if $FieldWarning %><p class="form__field-description form-text field-warning">{$FieldWarning}</p><% end_if %>

        <% if $Message %><p class="alert {$AlertType}" role="alert" id="message-{$ID}">{$Message}</p><% end_if %>

        <% if $Description %><p class="form__field-description form-text description">{$Description}</p><% end_if %>

        <div class="inputs">
            {$Field}
        </div>

    </div>

    <% if $RightTitle %><p class="form__field-extra-label" id="extra-label-{$ID}">$RightTitle</p><% end_if %>

</div>
