(function ($) {
	/** global: Craft */
	/** global: Garnish */
	Craft.TagEdit = Garnish.Base.extend({
		tagId: null,

		init: function (tagId, settings) {
			this.tagId = tagId;

			this.setSettings(settings, Craft.TagEdit.defaults);

			this.$deleteBtn = $('#delete-btn');

			this.addListener(this.$deleteBtn, 'click', 'showConfirmDeleteModal');
		},

		showConfirmDeleteModal: function (e) {
			e.originalEvent.preventDefault();

			if (this.confirmDeleteModal) {
				this.confirmDeleteModal.show();
				return;
			}

			Craft.postActionRequest(
				'tag-manager/cp/tag-summary',
				{ tagId: this.tagId },
				$.proxy(function (response, textStatus) {
					if (textStatus !== 'success')
						return;

					this.confirmDeleteModal = new Craft.DeleteTagModal(this.tagId, {
						contentSummary: response,
						redirect: this.settings.deleteModalRedirect,
					});
				}, this)
			);
		},
	}, {
		defaults: {
			deleteModalRedirect: null,
		},
	});
})(jQuery);