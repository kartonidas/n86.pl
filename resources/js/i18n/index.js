import { createI18n } from 'vue-i18n'
import I18nPL from './pl.js'

const i18n = createI18n({
    locale: 'pl',
    legacy: false,
    messages: {
        pl: I18nPL
    },
})

export default i18n