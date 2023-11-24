import RequiredMark from './RequiredMark.js'

export default {
    install (Vue) {
        Vue.directive('required', RequiredMark)
    }
}