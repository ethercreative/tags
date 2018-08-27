/* global Craft, $, defaultGroupHandle */

Craft.TagsIndex = Craft.BaseElementIndex.extend({

	editableGroups: null,
	$newTagBtnGroup: null,
	$newTagBtn: null,

	init: function (elementType, $container, settings) {
		this.on('selectSource', $.proxy(this, 'updateButton'));
		this.on('selectSite', $.proxy(this, 'updateButton'));
		this.base(elementType, $container, settings);
	},

	afterInit: function () {
		this.editableGroups = window.editableTagGroups;
		this.base();
	},

	getDefaultSourceKey: function () {
		if (
			this.settings.context === 'index'
			&& typeof defaultGroupHandle !== 'undefined'
		) {
			for (let i = 0, l = this.$sources.length; i < l; ++i) {
				const $source = $(this.$sources[i]);

				if ($source.data('handle') === defaultGroupHandle)
					return $source.data('key');
			}
		}

		return this.base();
	},

	updateButton: function () {
		if (!this.$source)
			return;

		const selectedSourceHandle = this.$source.data("handle")
			, isIndex = this.settings.context === "index";

		if (isIndex && typeof history !== "undefined") {
			let uri = "tags";

			if (selectedSourceHandle)
				uri += "/" + selectedSourceHandle;

			history.replaceState({}, "", Craft.getUrl(uri));
		}

		if (this.editableGroups.length === 0)
			return;

		if (this.$newTagBtnGroup)
			this.$newTagBtnGroup.remove();

		let selectedGroup;

		if (selectedSourceHandle) {
			let i = this.editableGroups.length;
			while (i--) {
				if (this.editableGroups[i].handle === selectedSourceHandle) {
					selectedGroup = this.editableGroups[i];
					break;
				}
			}
		}

		this.$newTagBtnGroup = $('<div class="btngroup submit" />');

		let $menuBtn, href, label;

		if (selectedGroup) {
			href  = this._getGroupTriggerHref(selectedGroup);
			label =
				isIndex
					? Craft.t("app", "New Tag")
					: Craft.t("app", "New {group} tag", { group: selectedGroup.name });

			this.$newTagBtn = $(
				'<a class="btn submit add icon" ' + href + '>'
					+ Craft.escapeHtml(label) +
				'</a>'
			).appendTo(this.$newTagBtnGroup);

			if (!isIndex) {
				this.addListener(this.$newTagBtn, "click", function (e) {
					this._openCreateTagModal(
						e.currentTarget.getAttribute("data-id")
					);
				});
			}

			if (this.editableGroups.length > 1) {
				$menuBtn = $('<div class="btn submit menubtn" />').appendTo(
					this.$newTagBtnGroup
				);
			}
		} else {
			this.$newTagBtn = $menuBtn = $(
				'<div class="btn submit add icon menubtn">'
					+ Craft.t("app", "New tag") +
				'</div>'
			).appendTo(this.$newTagBtnGroup);
		}

		if ($menuBtn) {
			let menuHtml = '<div class="menu"><ul>';

			for (let i = 0, l = this.editableGroups.length; i < l; ++i) {
				let group = this.editableGroups[i];

				if (isIndex || group.id !== selectedGroup.id) {
					href = this._getGroupTriggerHref(group);
					label =
						isIndex
							? group.name
							: Craft.t("app", "New {group} tag", { group: group.name });
					menuHtml +=
						'<li><a ' + href + '>'
							+ Craft.escapeHtml(label) +
						'</a></li>';
				}
			}

			menuHtml += '</ul></div>';

			$(menuHtml).appendTo(this.$newTagBtnGroup);
			const menuBtn = new Garnish.MenuBtn($menuBtn);

			if (!isIndex) {
				menuBtn.on("optionSelect", $.proxy(function (e) {
					this._openCreateTagModal(e.option.getAttribute("data-id"));
				}, this));
			}
		}

		this.addButton(this.$newTagBtnGroup);
	},

	_getGroupTriggerHref: function (group) {
		if (this.settings.context !== "index")
			return 'data-id="' + group.id + '"';

		let uri = 'tags/' + group.handle + '/new';

		if (this.siteId && this.siteId !== Craft.primarySiteId)
			for (let i = 0, l = Craft.sites.length; i < l; ++i)
				if (Craft.sites[i].id === this.siteId)
					uri += '/' + Craft.sites[i].handle;

		return 'href="' + Craft.getUrl(uri) + '"';
	},

	_openCreateTagModal: function (groupId) {
		if (this.$newTagBtn.hasClass("loading"))
			return;

		let group;
		for (let i = 0, l = this.editableGroups.length; i < l; ++i) {
			if (this.editableGroups[i].id === groupId) {
				group = this.editableGroups[i];
				break;
			}
		}

		if (!group)
			return;

		this.$newTagBtn.addClass("inactive");
		let newTagBtnText = this.$newTagBtn.text();
		this.$newTagBtn.text(
			Craft.t("app", "New {group} tag", { group: group.name })
		);

		Craft.createElementEditor(this.elementType, {
			hudTrigger: this.$newTagBtnGroup,
			elementType: "craft\\elements\\Tag",
			siteId: this.siteId,
			attributes: {
				groupId: groupId,
			},
			onBeginLoading: $.proxy(function () {
				this.$newTagBtn.addClass("loading");
			}, this),
			onEndLoading: $.proxy(function () {
				this.$newTagBtn.removeClass("loading");
			}, this),
			onHideHud: $.proxy(function () {
				this.$newTagBtn.removeClass("inactive").text(newTagBtnText);
			}, this),
			onSaveElement: $.proxy(function (response) {
				let groupSourceKey = "group:" + groupId;

				if (this.sourceKey !== groupSourceKey)
					this.selectSourceByKey(groupSourceKey);

				this.selectElementAfterUpdate(response.id);
				this.updateElements();
			}, this),
		});
	},

});

Craft.registerElementIndexClass(
	'ether\\tagManager\\elements\\Tag',
	Craft.TagsIndex
);