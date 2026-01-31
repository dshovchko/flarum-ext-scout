import app from 'flarum/admin/app';

app.initializers.add('dshovchko-scout', () => {
    app.extensionData.for('dshovchko-scout')
        .registerSetting({
            type: 'select',
            setting: 'clarkwinkelmann-scout.driver',
            options: {
                null: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.null'),
                algolia: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.algolia'),
                meilisearch: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.meilisearch'),
                tntsearch: app.translator.trans('clarkwinkelmann-scout.admin.setting.driverOption.tntsearch'),
            },
            default: 'null',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.driver'),
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.prefix',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.prefix'),
        })
        .registerSetting({
            type: 'switch',
            setting: 'clarkwinkelmann-scout.queue',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.queue'),
            help: app.translator.trans('clarkwinkelmann-scout.admin.setting.queueHelp'),
        })
        .registerSetting({
            type: 'number',
            setting: 'clarkwinkelmann-scout.limit',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.limit'),
            placeholder: app.translator.trans('clarkwinkelmann-scout.admin.setting.limitPlaceholder'),
            help: app.translator.trans('clarkwinkelmann-scout.admin.setting.limitHelp'),
        })
        .registerSetting({
            type: 'select',
            setting: 'clarkwinkelmann-scout.rankingStrategy',
            options: {
                default: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.default'),
                title_first: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.titleFirst'),
                exact_title_post: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.exactTitlePost'),
                title_only: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.titleOnly'),
                posts_only: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyOption.postsOnly'),
            },
            default: 'default',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategy'),
            help: app.translator.trans('clarkwinkelmann-scout.admin.setting.rankingStrategyHelp'),
        })
        .registerSetting({
            type: 'number',
            setting: 'clarkwinkelmann-scout.queryMinLength',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLength'),
            placeholder: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLengthPlaceholder'),
            help: app.translator.trans('clarkwinkelmann-scout.admin.setting.queryMinLengthHelp'),
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaId',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaId'),
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaSecret',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaSecret'),
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaConnectTimeout',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaConnectTimeout'),
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaReadTimeout',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaReadTimeout'),
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.algoliaWriteTimeout',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.algoliaWriteTimeout'),
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.meilisearchHost',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.meilisearchHost'),
            placeholder: '127.0.0.1:7700',
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.meilisearchKey',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.meilisearchKey'),
        })
        .registerSetting({
            type: 'number',
            setting: 'clarkwinkelmann-scout.tntsearchMaxDocs',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchMaxDocs'),
            placeholder: '500',
        })
        .registerSetting({
            type: 'switch',
            setting: 'clarkwinkelmann-scout.tntsearchFuzziness',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzziness'),
        })
        .registerSetting({
            type: 'number',
            setting: 'clarkwinkelmann-scout.tntsearchFuzzyDistance',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyDistance'),
            placeholder: '2',
        })
        .registerSetting({
            type: 'number',
            setting: 'clarkwinkelmann-scout.tntsearchFuzzyPrefixLength',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyPrefixLength'),
            placeholder: '50',
        })
        .registerSetting({
            type: 'text',
            setting: 'clarkwinkelmann-scout.tntsearchFuzzyMaxExpansions',
            label: app.translator.trans('clarkwinkelmann-scout.admin.setting.tntsearchFuzzyMaxExpansions'),
            placeholder: '2',
        });
});
