var TEMPLATE_SHAREDIALOGVIEW_RENDER_SURCHARGE =
   '<div class="resharerInfoView subView"></div>' +
        '{{#if isSharingAllowed}}' +
        '<ul class="subTabHeaders">' +
        '    <li class="subTabHeader selected subtab-localshare">{{localSharesLabel}}</li>' +
        '    <li class="subTabHeader subtab-publicshare">{{publicSharesLabel}}</li>' +
        '</ul>' +
        '<div class="tabsContainer">' +
        // TODO: this really should be a separate view class
        '    <div class="localShareView tab" style="padding-left:0;padding-right:0;">' +
        '        <label for="shareWith-{{cid}}" class="hidden-visually">{{shareLabel}}</label>' +
        '        <div class="oneline">' +
        '            <input id="shareWith-{{cid}}" class="shareWithField" type="text" placeholder="{{sharePlaceholder}}" />' +
        '            <span class="shareWithLoading icon-loading-small hidden"></span>'+
        '{{{remoteShareInfo}}}' +
        '        </div>' +
        '        <div class="shareeListView subView"></div>' +
        '        <div class="guestShareView subView"></div>' +
        '    </div>' +
        '    <div class="linkShareView subView tab hidden" style="padding-left:0;padding-right:0;"></div>' +
        '</div>' +
        '{{else}}' +
        '<div class="noSharingPlaceholder">{{noSharingPlaceholder}}</div>' +
        '{{/if}}' +
        '<div class="loading hidden" style="height: 50px"></div>';
    

var TEMPLATE_SHAREDIALOGVIEW_LISTSHARE_SURCHARGE = '<li data-share-id="{{shareId}}" data-share-type="{{shareType}}" data-share-with="{{shareWith}}">' +
    '<a href="#" class="unshare"><span class="icon-loading-small hidden"></span><span class="icon icon-delete"></span><span class="hidden-visually">{{unshareLabel}}</span></a>' +
    '{{#if avatarEnabled}}' +
    '<div class="avatar {{#if modSeed}}imageplaceholderseed{{/if}}" data-username="{{shareWith}}" {{#if modSeed}}data-seed="{{shareWith}} {{shareType}}"{{/if}}></div>' +
    '{{/if}}' +
    '<span class="has-tooltip username" title="{{shareWith}}">{{shareWithDisplayName}}</span>' +
    '{{#if mailNotificationEnabled}}  {{#unless isRemoteShare}}' +
    '<span class="shareOption">' +
        '<input id="mail-{{cid}}-{{shareWith}}" type="checkbox" name="mailNotification" class="mailNotification checkbox" {{#if wasMailSent}}checked="checked"{{/if}} />' +
        '<label for="mail-{{cid}}-{{shareWith}}">{{notifyByMailLabel}}</label>' +
    '</span>' +
    '{{/unless}} {{/if}}' +
    '{{#if isResharingAllowed}} {{#if sharePermissionPossible}}' +
    '<span class="shareOption">' +
        '<input id="canShare-{{cid}}-{{shareWith}}" type="checkbox" name="share" class="permissions checkbox" {{#if hasSharePermission}}checked="checked"{{/if}} data-permissions="{{sharePermission}}" />' +
        '<label for="canShare-{{cid}}-{{shareWith}}">{{canShareLabel}}</label>' +
    '</span>' +
    '{{/if}} {{/if}}' +
    '{{#if editPermissionPossible}}' +
    '<span class="shareOption">' +
        '<input id="canEdit-{{cid}}-{{shareWith}}" type="checkbox" name="edit" class="permissions checkbox" {{#if hasEditPermission}}checked="checked"{{/if}} />' +
        '<label for="canEdit-{{cid}}-{{shareWith}}">{{canEditLabel}}</label>' +
        '<a href="#" class="showCruds"><img class="svg" alt="{{crudsLabel}}" src="{{triangleSImage}}"/></a>' +
    '</span>' +
    '{{/if}}' +
    '<div class="cruds hidden">' +
        '{{#if createPermissionPossible}}' +
        '<span class="shareOption">' +
            '<input id="canCreate-{{cid}}-{{shareWith}}" type="checkbox" name="create" class="permissions checkbox" {{#if hasCreatePermission}}checked="checked"{{/if}} data-permissions="{{createPermission}}"/>' +
            '<label for="canCreate-{{cid}}-{{shareWith}}">{{createPermissionLabel}}</label>' +
        '</span>' +
        '{{/if}}' +
        '{{#if updatePermissionPossible}}' +
        '<span class="shareOption">' +
            '<input id="canUpdate-{{cid}}-{{shareWith}}" type="checkbox" name="update" class="permissions checkbox" {{#if hasUpdatePermission}}checked="checked"{{/if}} data-permissions="{{updatePermission}}"/>' +
            '<label for="canUpdate-{{cid}}-{{shareWith}}">{{updatePermissionLabel}}</label>' +
        '</span>' +
        '{{/if}}' +
        '{{#if deletePermissionPossible}}' +
        '<span class="shareOption">' +
            '<input id="canDelete-{{cid}}-{{shareWith}}" type="checkbox" name="delete" class="permissions checkbox" {{#if hasDeletePermission}}checked="checked"{{/if}} data-permissions="{{deletePermission}}"/>' +
            '<label for="canDelete-{{cid}}-{{shareWith}}">{{deletePermissionLabel}}</label>' +
        '</span>' +
        '{{/if}}' +
    '</div>' +
'</li>';

var TEMPLATE_SHAREDIALOGVIEW_LISTSHARE_GUEST_ITEM = '<li data-share-id="{{shareId}}" data-share-type="{{shareType}}" data-share-with="{{shareWith}}">' +
    '<a href="#" class="unshare"><span class="icon-loading-small hidden"></span><span class="icon icon-delete"></span><span class="hidden-visually">{{unshareLabel}}</span></a>' +
    '{{#if avatarEnabled}}' +
    '<div class="avatar {{#if modSeed}}imageplaceholderseed{{/if}}" data-username="{{shareWith}}" {{#if modSeed}}data-seed="{{shareWith}} {{shareType}}"{{/if}}></div>' +
    '{{/if}}' +
    '<span class="has-tooltip username" title="{{shareWith}}">{{shareWithDisplayName}}</span>' +
    '{{#if mailNotificationEnabled}}  {{#unless isRemoteShare}}' +
    '<span class="shareOption">' +
        '<input id="mail-{{cid}}-{{shareWith}}" type="checkbox" name="mailNotification" class="mailNotification checkbox" {{#if wasMailSent}}checked="checked"{{/if}} />' +
        '<label for="mail-{{cid}}-{{shareWith}}">{{notifyByMailLabel}}</label>' +
    '</span>' +
    '{{/unless}}' +
    '{{#if editPermissionPossible}}' +
    '<span class="shareOption">' +
        '<input id="canEdit-{{cid}}-{{shareWith}}" type="checkbox" name="edit" class="permissions checkbox" {{#if hasEditPermission}}checked="checked"{{/if}} />' +
        '<label for="canEdit-{{cid}}-{{shareWith}}">{{canEditLabel}}</label>' +
        '<a href="#" class="showCruds"><img class="svg" alt="{{crudsLabel}}" src="{{triangleSImage}}"/></a>' +
    '</span>' +
    '{{/if}}' +
    '<div class="cruds hidden">' +
        '{{#if createPermissionPossible}}' +
        '<span class="shareOption">' +
            '<input id="canCreate-{{cid}}-{{shareWith}}" type="checkbox" name="create" class="permissions checkbox" {{#if hasCreatePermission}}checked="checked"{{/if}} data-permissions="{{createPermission}}"/>' +
            '<label for="canCreate-{{cid}}-{{shareWith}}">{{createPermissionLabel}}</label>' +
        '</span>' +
        '{{/if}}' +
        '{{#if updatePermissionPossible}}' +
        '<span class="shareOption">' +
            '<input id="canUpdate-{{cid}}-{{shareWith}}" type="checkbox" name="update" class="permissions checkbox" {{#if hasUpdatePermission}}checked="checked"{{/if}} data-permissions="{{updatePermission}}"/>' +
            '<label for="canUpdate-{{cid}}-{{shareWith}}">{{updatePermissionLabel}}</label>' +
        '</span>' +
        '{{/if}}' +
        '{{#if deletePermissionPossible}}' +
        '<span class="shareOption">' +
            '<input id="canDelete-{{cid}}-{{shareWith}}" type="checkbox" name="delete" class="permissions checkbox" {{#if hasDeletePermission}}checked="checked"{{/if}} data-permissions="{{deletePermission}}"/>' +
            '<label for="canDelete-{{cid}}-{{shareWith}}">{{deletePermissionLabel}}</label>' +
        '</span>' +
        '{{/if}}' +
    '</div>' +
'</li>';