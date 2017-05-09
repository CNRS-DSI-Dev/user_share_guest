
(function() {

    // surchage pour afficher correctement le formulaire d'ajout d'invité
    
    var old_ShareDialogView_initialize = OC.Share.ShareDialogView.prototype.initialize;
    if (old_ShareDialogView_initialize) {
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
                shareeListView: 'ShareDialogShareeListView',
                /* SURCHARGE */
                guestView : 'UserShareGuestView' 
                /* FIN SURCHARGE */
            };
            this.existing_class = {};
            for(var name in subViews) {
                var className = subViews[name];
                /* SURCHARGE */
                if (_.isUndefined(options[name]) && OC.Share[className]) {
                    this[name] = new OC.Share[className](subViewOptions)
                    this.existing_class[name] = 1;
                } else {
                    this[name] = options[name];
                }
                /* FIN SURCHARGE*/
            }

            _.bindAll(this,
                'autocompleteHandler',
                '_onSelectRecipient',
                'onShareWithFieldChanged'
            );

            OC.Plugins.attach('OC.Share.ShareDialogView', this);
        }
    }

    var old_ShareDialogView_render = OC.Share.ShareDialogView.prototype.render;
    
    if (old_ShareDialogView_render) {
        OC.Share.ShareDialogView.prototype.render = function() {
            /* SURCHARGE */
            var baseTemplate = this._getTemplate('base', TEMPLATE_SHAREDIALOGVIEW_RENDER_SURCHARGE);
            /* FIN SURCHARGE */
            this.$el.html(baseTemplate({
                cid: this.cid,
                shareLabel: t('core', 'Share'),
                sharePlaceholder: this._renderSharePlaceholderPart(),
                remoteShareInfo: this._renderRemoteShareInfoPart(),
                isSharingAllowed: this.model.sharePermissionPossible(),
                localSharesLabel: t('core', 'User and Groups'),
                publicSharesLabel: t('core', 'Public Links'),
                noSharingPlaceholder: t('core', 'Resharing is not allowed')
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
                }).data('ui-autocomplete')._renderItem = _.bind(this.autocompleteRenderItem, this);
            }

            /* SURCHARGE */
            if (this.existing_class.resharerInfoView != undefined) {
                this.resharerInfoView.$el = this.$el.find('.resharerInfoView');
                this.resharerInfoView.render();    
            }

            if (this.existing_class.expirationView != undefined) {
                this.expirationView.$el = this.$el.find('.expirationView');
                this.expirationView.render();
            }

            if (this.existing_class.shareeListView != undefined) {
                this.shareeListView.$el = this.$el.find('.shareeListView');
                this.shareeListView.render();  
            }

            if (this.existing_class.mailView != undefined) {
                this.mailView.$el = this.$el.find('.mailView');
                this.mailView.render();   
            }

            if (this.existing_class.socialView != undefined) {
                this.socialView.$el = this.$el.find('.socialView');
                this.socialView.render();  
            }

            if (this.existing_class.guestView != undefined) {
                this.guestView.$el = this.$el.find('.guestShareView');
                this.guestView.render();
            }
            /* FIN SURCHARGE */

            this.$el.find('.hasTooltip').tooltip();

            return this;
        }
    }
})();
