/* global Craft, $, defaultGroupHandle */

Craft.DeleteTagModal = Garnish.Modal.extend({
	id: null,
	tagId: null,

	$deleteActionRadios: null,
	$deleteSpinner: null,

	tagSelect: null,
	_deleting: false,

	init: function (tagId, settings) {
		this.id = Math.floor(Math.random() * 1000000000);
		this.tagId = tagId;

		settings = $.extend(Craft.DeleteTagModal.defaults, settings);

		const isMultipleTags = Garnish.isArray(this.tagId);

		const $form = $(
			'<form class="modal fitted deleteusermodal" method="post">' +
				Craft.getCsrfInput() +
				'<input type="hidden" name="action" value="tag-manager/cp/delete" />' +
				(isMultipleTags
					? ""
					: '<input type="hidden" name="tagId" value="' + tagId + '" />'
				) +
				(settings.redirect
					? '<input type="hidden" name="redirect" value="' + settings.redirect + '" />'
					: ""
				) +
			'</form>'
		).appendTo(Garnish.$bod);

		const $body = $(
			'<div class="body">' +
				'<div class="content-summary">' +
					'<p>' + Craft.t("app", "What do you want to do with this tag?") + '</p>' +
					'<ul class="bullets" />' +
				'</div>' +
				'<div class="options">' +
					'<label>' +
						'<input type="radio" name="contentAction" value="replace" /> ' +
						Craft.t("app", "Replace with:") +
					'</label>' +
					'<div id="replaceselect' + this.id + '" class="elementselect">' +
						'<div class="elements" />' +
						'<div class="btn add icon dashed">' +
							Craft.t("app", "Choose a tag") +
						'</div>' +
					'</div>' +
					'<div>' +
						'<label class="error">' +
							'<input type="radio" name="contentAction" value="delete" /> ' +
							Craft.t("app", "Delete it") +
						'</label>' +
					'</div>' +
				'</div>' +
			'</div>'
		).appendTo($form);

		const $buttons = $('<div class="buttons right" />').appendTo($body);
		const $cancelBtn = $(
			'<div class="btn">' + Craft.t("app", "Cancel") + '</div>'
		).appendTo($buttons);

		if (settings.contentSummary.length)
			for (let i = 0, l = settings.contentSummary.length; i < l; ++i)
				$body.find('ul').append($('<li />', { text: settings.contentSummary[i] }));
		else
			$body.find('ul').remove();

		this.$deleteActionRadios = $body.find('input[type=radio]');
		this.$deleteSubmitBtn = $(
			'<input type="submit" class="btn submit disabled" value="' +
				(isMultipleTags
					? Craft.t("app", "Delete Tags")
					: Craft.t("app", "Delete Tag")
				) +
			'" />'
		).appendTo($buttons);
		this.$deleteSpinner = $('<div class="spinner hidden" />').appendTo($buttons);

		let idParam;
		if (isMultipleTags) {
			idParam = ['and'];
			for (let i = 0, l = this.tagId.length; i < l; ++i)
				idParam.push('not ' + this.tagId[i]);
		} else {
			idParam = 'not ' + this.tagId;
		}

		this.userSelect = new Craft.BaseElementSelectInput({
			id: 'replaceselect' + this.id,
			name: 'replaceWith',
			elementType: 'ether\\tagManager\\elements\\Tag',
			criteria: {
				id: idParam,
			},
			limit: 1,
			modalSettings: {
				closeOtherModals: false,
			},
			onSelectElements: $.proxy(function () {
				this.updateSizeAndPosition();
				if (!this.$deleteActionRadios.first().prop("checked"))
					this.$deleteActionRadios.first().trigger("click");
				else
					this.validateDeleteInputs();
			}, this),
			onRemoveElements: $.proxy(this, 'validateDeleteInputs'),
			selectable: false,
			editable: false,
		});

		this.addListener($cancelBtn, "click", "hide");
		this.addListener(this.$deleteActionRadios, "change", "validateDeleteInputs");
		this.addListener($form, "submit", "handleSubmit");

		this.base($form, settings);
	},

	validateDeleteInputs: function () {
		let validates = false;

		if (this.$deleteActionRadios.eq(0).prop("checked"))
			validates = !!this.userSelect.totalSelected;
		else if (this.$deleteActionRadios.eq(1).prop("checked"))
			validates = true;

		if (validates)
			this.$deleteSubmitBtn.removeClass("disabled");
		else
			this.$deleteSubmitBtn.addClass("disabled");

		return validates;
	},

	handleSubmit: function (e) {
		if (this._deleting || !this.validateDeleteInputs()) {
			e.preventDefault();
			return;
		}

		this.$deleteSubmitBtn.addClass("active");
		this.$deleteSpinner.removeClass("hidden");
		this.disable();
		this.userSelect.disable();
		this._deleting = true;

		if (this.settings.onSubmit() === false)
			e.preventDefault();
	},

	onFadeIn: function () {
		if (!Garnish.isMobileBrowser(true))
			this.$deleteActionRadios.first().trigger("focus");

		this.base();
	},

}, {
	defaults: {
		contentSummary: [],
		onSubmit: $.noop,
		redirect: null,
	},
});