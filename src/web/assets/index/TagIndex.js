/* global Craft, $, defaultGroupHandle */

Craft.Tags = Craft.Tags || {};

Craft.Tags.Index = Craft.BaseElementIndex.extend({
	init: function (elementType, $container, settings) {
		this.on('selectSource', $.proxy(this, 'updateUrl'));
		this.on('selectSite', $.proxy(this, 'updateUrl'));
		this.base(elementType, $container, settings);
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

	updateUrl: function () {
		if (!this.$source)
			return;

		const selectedSourceHandle = this.$source.data('handle');

		if (this.settings.context === 'index' && typeof history !== 'undefined') {
			let uri = 'tags';

			if (selectedSourceHandle)
				uri += '/' + selectedSourceHandle;

			history.replaceState({}, '', Craft.getUrl(uri));
		}
	}
});

Craft.registerElementIndexClass(
	'ether\\tagManager\\elements\\Tag',
	Craft.Tags.Index
);