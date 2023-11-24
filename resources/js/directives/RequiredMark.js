const RequiredMark = {
    created(el) {
        el.insertAdjacentHTML('beforeEnd', '<span class="required-mark">*</span>')
    },
}

export default RequiredMark