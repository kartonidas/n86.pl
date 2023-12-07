import * as validators from '@vuelidate/validators'
import i18n from "@/i18n"

const { createI18nMessage } = validators

const withI18nMessage = createI18nMessage({ t: i18n.global.t.bind(i18n) })

export const required = withI18nMessage(validators.required)
export const email = withI18nMessage(validators.email)
export const sameAs = withI18nMessage(validators.sameAs, { withArguments: true })
export const requiredIf = withI18nMessage(validators.requiredIf, { withArguments: true })
export const minValue = withI18nMessage(validators.minValue, { withArguments: true })
