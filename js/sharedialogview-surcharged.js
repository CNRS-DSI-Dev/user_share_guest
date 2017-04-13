/*
    Surcharge de méthode du coeur js
    /!\ voir avec esteban pour l'astuce du old
 */
(function() {
    var TEMPLATE_SHAREDIALOGVIEW_RENDER_SURCHARGE =
        '<div class="resharerInfoView subView"></div>' +
        '{{#if isSharingAllowed}}' +
        '<label for="shareWith-{{cid}}" class="hidden-visually">{{shareLabel}}</label>' +
        '<div class="oneline">' +
        '    <input id="shareWith-{{cid}}" class="shareWithField" type="text" placeholder="{{sharePlaceholder}}" />' +
        '    <span class="shareWithLoading icon-loading-small hidden"></span>'+
        '{{{remoteShareInfo}}}' +
        '</div>' +
        '{{/if}}' +
        '<div class="shareeListView subView"></div>' +
        '<div class="linkShareView subView"></div>' +
        '<div class="expirationView subView"></div>' +
        '<div class="mailView subView"></div>' +
        '<div class="socialView subView"></div>' +
        '<div class="guestShareView subView"></div>' + // partie du template modifiée
        '<div class="loading hidden" style="height: 50px"></div>';


    // surchage pour afficher correctement le formulaire d'ajout d'invité
    
    var old_initialize = OC.Share.ShareDialogView.prototype.initialize;

    OC.Share.ShareDialogView.prototype.initialize = function(options) {
        var view = this;
        this.model.on('fetchError', function() {
            OC.Notification.showTemporary(t('core', 'Share details could not be loaded for this item.'));
        });

        if(!_.isUndefined(options.configModel)) {
            this.configModel = options.configModel;
        } else {
            throw 'missing OC.Share.ShareConfigModel';
        }

        this.configModel.on('change:isRemoteShareAllowed', function() {
            view.render();
        });
        this.model.on('change:permissions', function() {
            view.render();
        });

        this.model.on('request', this._onRequest, this);
        this.model.on('sync', this._onEndRequest, this);

        var subViewOptions = {
            model: this.model,
            configModel: this.configModel
        };

        var subViews = {
            resharerInfoView: 'ShareDialogResharerInfoView',
            linkShareView: 'ShareDialogLinkShareView',
            expirationView: 'ShareDialogExpirationView',
            shareeListView: 'ShareDialogShareeListView',
            mailView: 'ShareDialogMailView',
            socialView: 'ShareDialogLinkSocialView',
            guestView : 'UserShareGuestView' // surcharge a cet endroit
        };
        for(var name in subViews) {
            var className = subViews[name];
            this[name] = _.isUndefined(options[name])
                ? new OC.Share[className](subViewOptions)
                : options[name];
        }

        _.bindAll(this,
            'autocompleteHandler',
            '_onSelectRecipient',
            'onShareWithFieldChanged'
        );
    }

    var old_render = OC.Share.ShareDialogView.prototype.render;

    OC.Share.ShareDialogView.prototype.render = function() {
        var baseTemplate = this._getTemplate('base', TEMPLATE_SHAREDIALOGVIEW_RENDER_SURCHARGE);

        this.$el.html(baseTemplate({
            cid: this.cid,
            shareLabel: t('core', 'Share'),
            sharePlaceholder: this._renderSharePlaceholderPart(),
            remoteShareInfo: this._renderRemoteShareInfoPart(),
            isSharingAllowed: this.model.sharePermissionPossible()
        }));

        var $shareField = this.$el.find('.shareWithField');
        if ($shareField.length) {
            $shareField.autocomplete({
                minLength: 1,
                delay: 750,
                focus: function(event) {
                    event.preventDefault();
                },
                source: this.autocompleteHandler,
                select: this._onSelectRecipient
            }).data('ui-autocomplete')._renderItem = this.autocompleteRenderItem;
        }

        this.resharerInfoView.$el = this.$el.find('.resharerInfoView');
        this.resharerInfoView.render();

        this.linkShareView.$el = this.$el.find('.linkShareView');
        this.linkShareView.render();

        this.expirationView.$el = this.$el.find('.expirationView');
        this.expirationView.render();

        this.shareeListView.$el = this.$el.find('.shareeListView');
        this.shareeListView.render();

        this.mailView.$el = this.$el.find('.mailView');
        this.mailView.render();

        this.socialView.$el = this.$el.find('.socialView');
        this.socialView.render();
        
        /* début surcharge */
        this.guestView.$el = this.$el.find('.guestShareView');
        this.guestView.render();
        /* fin surcharge */

        this.$el.find('.hasTooltip').tooltip();

        return this;
    }

    

    // surcharge pour l'affichage du listing des user auxquels le fichier a été partagé

})();
